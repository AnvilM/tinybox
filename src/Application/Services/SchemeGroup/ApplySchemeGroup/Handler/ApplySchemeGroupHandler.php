<?php

declare(strict_types=1);

namespace App\Application\Services\SchemeGroup\ApplySchemeGroup\Handler;

use App\Application\Services\SchemeGroup\ApplySchemeGroup\Command\ApplySchemeGroupCommand;
use App\Application\Services\Subscription\ApplySubscription\Exception\UnableToRestartSingBoxServiceException;
use App\Application\Shared\SchemeGroup\UseCase\ReadSchemeGroupsList\ReadSchemeGroupsListUseCase;
use App\Application\Shared\Shared\Utils\UseCase\CreateSingBoxConfig\CreateSingBoxConfigUseCase;
use App\Application\Shared\Shared\Utils\UseCase\RestartSingBoxService\RestartSingBoxServiceUseCase;
use App\Domain\Outbound\Collection\OutboundMap;
use App\Domain\Outbound\Exception\OutboundAlreadyExistsException;
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
        private ReadSchemeGroupsListUseCase  $readSchemeGroupsListUseCase,
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
         * Read list of all schemeGroups
         */
        $schemeGroups = $this->readSchemeGroupsListUseCase->handle();


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
            } catch (OutboundAlreadyExistsException|InvalidArgumentException) {
                continue;
                // TODO: Add reporter event
            }
        }

        /**
         * Create sing-box schemeGroup from outbounds map
         */
        $singBoxSchemeGroup = $this->createSingBoxConfigUseCase->handle($outboundsMap);


        /**
         * Try to save schemeGroup file
         */
        try {
            $this->saveFilePort->save(
                $this->configInstancePort->get()->singBoxConfig->defaultConfigPath,
                $singBoxSchemeGroup
            );
        } catch (UnableToSaveFileException) {
            throw new CriticalException("Unable to save the schemeGroup file");
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