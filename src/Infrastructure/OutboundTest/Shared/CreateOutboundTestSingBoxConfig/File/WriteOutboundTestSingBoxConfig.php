<?php

declare(strict_types=1);

namespace App\Infrastructure\OutboundTest\Shared\CreateOutboundTestSingBoxConfig\File;

use App\Domain\Shared\Exception\File\UnableToSaveFileException;
use App\Domain\Shared\Ports\Config\ConfigInstancePort;
use App\Domain\Shared\Ports\IO\File\SaveFileNotifyPort;

final readonly class WriteOutboundTestSingBoxConfig
{
    public function __construct(
        private ConfigInstancePort $configInstancePort,
        private SaveFileNotifyPort $saveFileNotifyPort,
    )
    {
    }

    /**
     * @param string $config Outbound test sing-box config to write
     *
     * @throws UnableToSaveFileException If unable to write file
     */
    public function write(string $config): void
    {
        $this->saveFileNotifyPort->notifyStartAndSuccess(
            "Writing outbound test sing-box config file...",
            "Outbound test sing-box config successfully saved"
        )->save($this->configInstancePort->get()->singBoxConfig->outboundTest->singBoxConfig, $config);
    }
}