<?php

declare(strict_types=1);

namespace App\Application\Group\UseCase\ApplyGroup;

use App\Application\Exception\Repository\Shared\UnableToGetListException;
use App\Application\Repository\Group\GetGroupListRepository;
use App\Application\Shared\Exception\UseCase\RestartSingBox\UnableToRestartSingBoxServiceException;
use App\Application\Shared\UseCase\CreateSingBoxConfig\CreateSingBoxConfigUseCase;
use App\Application\Shared\UseCase\RestartSingBoxService\RestartSingBoxServiceUseCase;
use App\Domain\Group\Exception\GroupNotFoundException;
use App\Domain\Outbound\Collection\OutboundMap;
use App\Domain\Outbound\Exception\OutboundAlreadyExistsException;
use App\Domain\Outbound\Exception\UnsupportedOutboundTypeException;
use App\Domain\Outbound\Factory\FromScheme\FromSchemeOutboundFactory;
use App\Domain\Shared\Exception\CriticalException;
use App\Domain\Shared\Exception\File\UnableToSaveFileException;
use App\Domain\Shared\Ports\Config\ConfigInstancePort;
use App\Domain\Shared\Ports\IO\File\SaveFilePort;
use App\Domain\Shared\VO\Shared\NonEmptyStringVO;
use InvalidArgumentException;

final readonly class ApplyGroupUseCase
{
    public function __construct(
        private GetGroupListRepository       $getGroupListRepository,
        private CreateSingBoxConfigUseCase   $createSingBoxConfigUseCase,
        private SaveFilePort                 $saveFilePort,
        private ConfigInstancePort           $configInstancePort,
        private RestartSingBoxServiceUseCase $restartSingBoxServiceUseCase,
    )
    {
    }

    /**
     * @throws CriticalException
     */
    public function handle(string $groupName): void
    {
        /**
         * Try to read list of all groups
         */
        try {
            $groups = $this->getGroupListRepository->getGroupsList();
        } catch (UnableToGetListException $e) {
            throw new CriticalException("Unable to apply group", $e->getDebugMessage());
        }


        /**
         * Try to create group name
         */
        try {
            $groupName = new NonEmptyStringVO($groupName);
        } catch (InvalidArgumentException) {
            throw new CriticalException("Invalid group name: $groupName");
        }


        /**
         * Try to find group with provided name
         */
        try {
            $group = $groups->getByName($groupName);
        } catch (GroupNotFoundException) {
            throw new CriticalException("Group with name {$groupName->getValue()} not found");
        }


        /**
         * Create empty outbounds map
         */
        $outboundsMap = new OutboundMap();


        foreach ($group->getOutbounds()->getMap() as $outbound) {
            /**
             * Try to create outbound from outbound and add it to outbounds map
             */
            try {
                $outboundsMap->add(FromSchemeOutboundFactory::fromScheme($outbound, $outboundsMap->count()));
            } catch (OutboundAlreadyExistsException|InvalidArgumentException|UnsupportedOutboundTypeException) {
                continue;
                // TODO: Add reporter event
            }
        }

        /**
         * Create sing-box group from outbounds map
         */
        $singBoxGroup = $this->createSingBoxConfigUseCase->handle($outboundsMap);


        /**
         * Try to save sing-box config file
         */
        try {
            $this->saveFilePort->save(
                $this->configInstancePort->get()->singBoxConfig->defaultConfigPath,
                $singBoxGroup
            );
        } catch (UnableToSaveFileException) {
            throw new CriticalException("Unable to save the sing-box config  file");
        }


        /**
         * Try to restart sing box service
         */
        try {
            $this->restartSingBoxServiceUseCase->handle();
        } catch (UnableToRestartSingBoxServiceException) {
            throw new CriticalException("Unable to restart sing-box service");
        }


    }
}