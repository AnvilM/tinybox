<?php

declare(strict_types=1);

namespace App\Application\Shared\UseCase\CreateConfig\FIle;

use App\Application\Shared\DTO\UseCase\CreateConfig\ConfigType;
use App\Domain\Shared\Exception\File\UnableToReadFileException;
use App\Domain\Shared\Exception\Json\UnableToDecodeJsonException;
use App\Domain\Shared\Ports\Config\ConfigInstancePort;
use App\Domain\Shared\Ports\IO\File\ReadJsonFileNotifyPort;

final readonly class ReadOutboundTemplate
{
    public function __construct(
        private ReadJsonFileNotifyPort $readJsonFileNotifyPort,
        private ConfigInstancePort     $configInstancePort
    )
    {
    }


    /**
     * Read outbound template file
     *
     * @param ConfigType $configType Config type
     *
     * @return array Outbound template as JSON decoded array
     *
     * @throws UnableToReadFileException If unable to read file
     * @throws UnableToDecodeJsonException If unable to decode JSON
     *
     */
    public function read(ConfigType $configType): array
    {
        return match ($configType) {
            ConfigType::SingBox => $this->readJsonFileNotifyPort->notifyStartAndSuccess(
                "Reading sing-box outbound template file...",
                "Sing-box outbound template file successfully read"
            )->read($this->configInstancePort->get()->singBoxConfig->templates->outbound),
            ConfigType::Xray => $this->readJsonFileNotifyPort->notifyStartAndSuccess(
                "Reading xray outbound template file...",
                "Xray outbound template file successfully read"
            )->read($this->configInstancePort->get()->xrayConfig->templates->outbound),
            default => throw new UnableToReadFileException()
        };
    }
}