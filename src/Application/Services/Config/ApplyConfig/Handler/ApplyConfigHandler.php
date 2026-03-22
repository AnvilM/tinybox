<?php

declare(strict_types=1);

namespace App\Application\Services\Config\ApplyConfig\Handler;

use App\Application\Services\Config\ApplyConfig\Command\ApplyConfigCommand;
use App\Application\Services\Subscription\ApplySubscription\Exception\UnableToRestartSingBoxServiceException;
use App\Application\Shared\Config\UseCase\ReadConfigsList\ReadConfigsListUseCase;
use App\Application\Shared\Shared\Utils\UseCase\CreateSingBoxConfig\CreateSingBoxConfigUseCase;
use App\Application\Shared\Shared\Utils\UseCase\RestartSingBoxService\RestartSingBoxServiceUseCase;
use App\Domain\Config\Exception\ConfigNotFoundException;
use App\Domain\Config\Exception\InvalidConfigNameException;
use App\Domain\Config\VO\ConfigNameVO;
use App\Domain\Outbound\Collection\OutboundMap;
use App\Domain\Outbound\Exception\OutboundAlreadyExistsException;
use App\Domain\Outbound\Factory\OutboundFactory;
use App\Domain\Shared\Exception\CriticalException;
use App\Domain\Shared\Exception\File\UnableToSaveFileException;
use App\Domain\Shared\Ports\Config\ConfigInstancePort;
use App\Domain\Shared\Ports\IO\File\SaveFilePort;
use InvalidArgumentException;

final readonly class ApplyConfigHandler
{
    public function __construct(
        private ReadConfigsListUseCase       $readConfigsListUseCase,
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
    public function handle(ApplyConfigCommand $command): void
    {
        /**
         * Read list of all configs
         */
        $configs = $this->readConfigsListUseCase->handle();


        /**
         * Try to create config name
         */
        try {
            $configName = new ConfigNameVO($command->configName);
        } catch (InvalidConfigNameException) {
            throw new CriticalException('Invalid config name: ' . $command->configName);
        }


        /**
         * Try to find config with provided name
         */
        try {
            $config = $configs->getByName($configName);
        } catch (ConfigNotFoundException) {
            throw new CriticalException('Config with name ' . $configName->getName() . ' not found');
        }


        /**
         * Create empty outbounds map
         */
        $outboundsMap = new OutboundMap();


        foreach ($config->getSchemes()->getMap() as $scheme) {
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
         * Create sing-box config from outbounds map
         */
        $singBoxConfig = $this->createSingBoxConfigUseCase->handle($outboundsMap);


        /**
         * Try to save config file
         */
        try {
            $this->saveFilePort->save(
                $this->configInstancePort->get()->singBoxConfig->defaultConfigPath,
                $singBoxConfig
            );
        } catch (UnableToSaveFileException) {
            throw new CriticalException("Unable to save the configuration file");
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