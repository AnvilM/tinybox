<?php

declare(strict_types=1);

namespace App\Application\Repository\Outbound\Shared\File;

use App\Domain\Outbound\Collection\OutboundMap;
use App\Domain\Shared\Exception\File\UnableToSaveFileException;
use App\Domain\Shared\Exception\Json\UnableToEncodeJsonException;
use App\Domain\Shared\Ports\Config\ConfigInstancePort;
use App\Domain\Shared\Ports\IO\File\SaveFileNotifyPort;

final readonly class WriteOutbounds
{
    public function __construct(
        private SaveFileNotifyPort $saveFileNotifyPort,
        private ConfigInstancePort $configInstancePort,
    )
    {
    }

    /**
     * Write outbounds map to outbounds file
     *
     * @throws UnableToEncodeJsonException If unable to convert outbounds map to JSON
     * @throws UnableToSaveFileException If unable to save outbounds to file
     */
    public function write(OutboundMap $outboundsMap): void
    {
        $path = $this->configInstancePort->get()->outboundsListPath;

        $this->saveFileNotifyPort->notifyStartAndSuccess(
            "Saving outbounds...",
            "Outbounds successfully saved",
        )->save($path, $outboundsMap->toJson());
    }
}