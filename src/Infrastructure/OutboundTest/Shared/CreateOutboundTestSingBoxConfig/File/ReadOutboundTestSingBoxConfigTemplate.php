<?php

declare(strict_types=1);

namespace App\Infrastructure\OutboundTest\Shared\CreateOutboundTestSingBoxConfig\File;

use App\Domain\Shared\Exception\File\UnableToReadFileException;
use App\Domain\Shared\Exception\Json\UnableToDecodeJsonException;
use App\Domain\Shared\Ports\Config\ConfigInstancePort;
use App\Domain\Shared\Ports\IO\File\ReadJsonFileNotifyPort;

final readonly class ReadOutboundTestSingBoxConfigTemplate
{
    public function __construct(
        private ReadJsonFileNotifyPort $readJsonFileNotifyPort,
        private ConfigInstancePort     $configInstancePort
    )
    {
    }


    /**
     * Read outbound test sing-box config template file
     *
     * @return array Outbound test sing-Box config template as JSON decoded array
     *
     * @throws UnableToReadFileException If unable to read file
     * @throws UnableToDecodeJsonException If unable to decode JSON
     *
     */
    public function read(): array
    {
        return $this->readJsonFileNotifyPort->notifyStartAndSuccess(
            "Reading outbound test sing-box config template file...",
            "Outbound test sing-Box config template file successfully read"
        )->read($this->configInstancePort->get()->singBoxConfig->outboundTest->templates->config);
    }
}