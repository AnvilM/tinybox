<?php

declare(strict_types=1);

namespace App\Application\Services\SchemeGroup\ApplySchemeGroup\Handler;

use App\Application\Exception\Repository\Shared\UnableToGetListException;
use App\Application\Repository\SchemeGroup\GetSchemeGroupListRepository;
use App\Application\Services\SchemeGroup\ApplySchemeGroup\Command\ApplySchemeGroupCommand;
use App\Application\Services\Subscription\ApplySubscription\Exception\UnableToRestartSingBoxServiceException;
use App\Application\Shared\UseCase\CreateSingBoxConfig\CreateSingBoxConfigUseCase;
use App\Application\Shared\UseCase\RestartSingBoxService\RestartSingBoxServiceUseCase;
use App\Domain\Outbound\Collection\OutboundMap;
use App\Domain\Outbound\Exception\OutboundAlreadyExistsException;
use App\Domain\Outbound\Exception\UnsupportedOutboundTypeException;
use App\Domain\Outbound\Factory\OutboundFactory;
use App\Domain\SchemeGroup\Exception\SchemeGroupNotFoundException;
use App\Domain\Shared\Exception\CriticalException;
use App\Domain\Shared\Exception\File\UnableToSaveFileException;
use App\Domain\Shared\Ports\Config\ConfigInstancePort;
use App\Domain\Shared\Ports\IO\File\SaveFilePort;
use App\Domain\Shared\VO\Shared\NonEmptyStringVO;
use InvalidArgumentException;

final readonly class ApplySchemeGroupHandler
{
    public function __construct(
        private GetSchemeGroupListRepository $getSchemeGroupListRepository,
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
    public function handle(ApplySchemeGroupCommand $command): void
    {
        /**
         * Try to read list of all schemeGroups
         */
        try {
            $schemeGroups = $this->getSchemeGroupListRepository->getSchemeGroupsList();
        } catch (UnableToGetListException $e) {
            throw new CriticalException("Unable to apply scheme group", $e->getDebugMessage());
        }


        /**
         * Try to create schemeGroup name
         */
        try {
            $schemeGroupName = new NonEmptyStringVO($command->schemeGroupName);
        } catch (InvalidArgumentException) {
            throw new CriticalException('Invalid schemeGroup name: ' . $command->schemeGroupName);
        }


        /**
         * Try to find schemeGroup with provided name
         */
        try {
            $schemeGroup = $schemeGroups->getByName($schemeGroupName);
        } catch (SchemeGroupNotFoundException) {
            throw new CriticalException('SchemeGroup with name ' . $schemeGroupName->getValue() . ' not found');
        }


        /**
         * Create empty outbounds map
         */
        $outboundsMap = new OutboundMap();


        foreach ($schemeGroup->getSchemes()->getMap() as $scheme) {
            /**
             * Try to create outbound from scheme and add it to outbounds map
             */
            try {
                $outboundsMap->add(OutboundFactory::fromScheme($scheme));
            } catch (OutboundAlreadyExistsException|InvalidArgumentException|UnsupportedOutboundTypeException) {
                continue;
                // TODO: Add reporter event
            }
        }

        /**
         * Create sing-box schemeGroup from outbounds map
         */
        $singBoxSchemeGroup = $this->createSingBoxConfigUseCase->handle($outboundsMap);


        /**
         * Try to save sing-box config file
         */
        try {
            $this->saveFilePort->save(
                $this->configInstancePort->get()->singBoxConfig->defaultConfigPath,
                $singBoxSchemeGroup
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