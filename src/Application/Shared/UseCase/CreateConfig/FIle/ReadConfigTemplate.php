<?php

declare(strict_types=1);

namespace App\Application\Shared\UseCase\CreateConfig\FIle;

use App\Application\Shared\DTO\UseCase\CreateConfig\ConfigType;
use App\Domain\Shared\Exception\File\UnableToReadFileException;
use App\Domain\Shared\Exception\Json\UnableToDecodeJsonException;
use App\Domain\Shared\Ports\Config\ConfigInstancePort;
use App\Domain\Shared\Ports\IO\File\ReadJsonFileNotifyPort;

final readonly class ReadConfigTemplate
{
    public function __construct(
        private ReadJsonFileNotifyPort $readJsonFileNotifyPort,
        private ConfigInstancePort     $configInstancePort
    )
    {
    }


    /**
     * Read sing-box config template file
     *
     * @param ConfigType $configType Config type
     *
     * @return array Sing-Box config template as JSON decoded array
     *
     * @throws UnableToReadFileException If unable to read file
     * @throws UnableToDecodeJsonException If unable to decode JSON
     *
     */
    public function read(ConfigType $configType): array
    {
        return match ($configType) {
            ConfigType::SingBox => $this->readJsonFileNotifyPort->notifyStartAndSuccess(
                "Reading sing-box config template file...",
                "Sing-Box Group template file successfully read"
            )->read($this->configInstancePort->get()->singBoxConfig->templates->config),
            ConfigType::Xray => $this->readJsonFileNotifyPort->notifyStartAndSuccess(
                "Reading xray config template file...",
                "Xray config template file successfully read"
            )->read($this->configInstancePort->get()->xrayConfig->templates->config),
            default => throw new UnableToReadFileException()
        };
    }
}