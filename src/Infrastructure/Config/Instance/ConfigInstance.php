<?php

declare(strict_types=1);

namespace App\Infrastructure\Config\Instance;

use App\Domain\Shared\Exception\File\UnableToReadFileException;
use App\Domain\Shared\Exception\Json\UnableToDecodeJsonException;
use App\Domain\Shared\Ports\Config\ConfigInstancePort;
use App\Domain\Shared\Ports\IO\File\ReadJsonFileNotifyPort;
use App\Domain\Shared\Ports\IO\Reporter\ReporterPort;
use App\Domain\Shared\Ports\OS\Path\NormalizePathPort;
use App\Domain\Shared\ReporterEvent\Events\Shared\Config\ConfigFileReadFailedReporterEvent;
use App\Domain\Shared\VO\Config\ConfigVO;
use App\Infrastructure\Config\Factory\ConfigFactory;
use App\Infrastructure\Config\Factory\DefaultConfigFactory;

final readonly class ConfigInstance implements ConfigInstancePort
{
    private ConfigVO $config;

    public function __construct(
        private ReadJsonFileNotifyPort $readJsonFileNotifyPort,
        private ConfigFactory          $configFactory,
        private DefaultConfigFactory   $defaultConfigFactory,
        private ReporterPort           $reporterPort,
        private NormalizePathPort      $normalizePathPort,
    )
    {
    }

    public function get(): ConfigVO
    {
        return $this->config;
    }

    public function set(?string $configPath): void
    {
        try {
            if ($configPath === null) throw new UnableToReadFileException();

            $rawConfig = $this->readJsonFileNotifyPort
                ->notifyStartAndSuccess(
                    "Reading configuration file...",
                    "Configuration file successfully read"
                )->read($this->normalizePathPort->execute($configPath));
        } catch (UnableToReadFileException|UnableToDecodeJsonException) {
            $rawConfig = [];

            $this->reporterPort->notify(new ConfigFileReadFailedReporterEvent());
        }

        $this->config = $this->configFactory->create(
            $rawConfig,
            $this->defaultConfigFactory->create()
        );
    }
}