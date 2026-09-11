<?php

declare(strict_types=1);

namespace App\Application\Repository\Outbound\Shared\File;

use App\Domain\Shared\Exception\File\UnableToSaveFileException;
use App\Domain\Shared\Exception\Json\UnableToEncodeJsonException;
use App\Domain\Shared\Ports\Config\ConfigInstancePort;
use App\Domain\Shared\Ports\IO\File\SaveFileNotifyPort;
use JsonException;

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
    public function write(array $outbounds): void
    {
        $path = $this->configInstancePort->get()->outboundsListPath;


        /**
         * Try to convert array to JSON
         */
        try {
            $json = json_encode(
                $outbounds,
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR
            );
        } catch (JsonException) {
            throw new UnableToEncodeJsonException();
        }


        $this->saveFileNotifyPort->notifyStartAndSuccess(
            "Saving outbounds...",
            "Outbounds successfully saved",
        )->save($path, $json);
    }
}