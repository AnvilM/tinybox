<?php

declare(strict_types=1);

namespace App\Infrastructure\Config\Factory;

use App\Core\Shared\Exception\File\UnableToDecodeJSONException;
use App\Core\Shared\Exception\File\UnableToReadFileException;
use App\Core\Shared\Ports\Config\ConfigFactoryPort;
use App\Core\Shared\Ports\File\JsonReaderPort;
use App\Core\Shared\Ports\Reporter\ReporterPort;
use App\Core\Shared\ReporterEvent\Events\Shared\Config\ConfigFileReadFailedReporterEvent;
use App\Core\Shared\ReporterEvent\Events\Shared\Config\ConfigFileReadSuccessfullyReporterEvent;
use App\Core\Shared\ReporterEvent\Events\Shared\Config\StartReadingConfigFileReporterEvent;
use App\Core\Shared\VO\Config\ConfigVO;
use App\Infrastructure\Config\Mapper\RawConfigMapper;
use Application\Config\ApplicationConfig\ApplicationConfig;

final readonly class ConfigFactory implements ConfigFactoryPort
{

    private ConfigVO $config;

    public function __construct(
        private JsonReaderPort       $jsonReaderPort,
        private RawConfigMapper      $rawConfigMapper,
        private DefaultConfigFactory $defaultConfigFactory,
        private ReporterPort         $reporterPort,
    )
    {
        $this->reporterPort->notify(new StartReadingConfigFileReporterEvent(
            ApplicationConfig::baseConfigFilePath()
        ));

        try {
            $rawConfig = $this->jsonReaderPort->read(
                ApplicationConfig::baseConfigFilePath(),
            );

            $this->reporterPort->notify(new ConfigFileReadSuccessfullyReporterEvent());

        } catch (UnableToReadFileException|UnableToDecodeJSONException) {
            $rawConfig = [];

            $this->reporterPort->notify(new ConfigFileReadFailedReporterEvent());
        }

        $this->config = $this->rawConfigMapper->map(
            $rawConfig,
            $this->defaultConfigFactory->create()
        );
    }

    public function get(): ConfigVO
    {
        return $this->config;
    }
}