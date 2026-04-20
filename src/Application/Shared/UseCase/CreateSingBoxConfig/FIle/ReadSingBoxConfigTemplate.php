<?php

declare(strict_types=1);

namespace App\Application\Shared\UseCase\CreateSingBoxConfig\FIle;

use App\Domain\Shared\Exception\File\UnableToReadFileException;
use App\Domain\Shared\Exception\Json\UnableToDecodeJsonException;
use App\Domain\Shared\Ports\Config\ConfigInstancePort;
use App\Domain\Shared\Ports\IO\File\ReadJsonFileNotifyPort;

final readonly class ReadSingBoxConfigTemplate
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
     * @return array Sing-Box config template as JSON decoded array
     *
     * @throws UnableToReadFileException If unable to read file
     * @throws UnableToDecodeJsonException If unable to decode JSON
     *
     */
    public function read(): array
    {
        return $this->readJsonFileNotifyPort->notifyStartAndSuccess(
            "Reading sing-box config template file...",
            "Sing-Box Group template file successfully read"
        )->read($this->configInstancePort->get()->singBoxConfig->templates->config);
    }
}