<?php

declare(strict_types=1);

namespace App\Application\GenerateConfigs\File;

use App\Core\Shared\Exception\CriticalException;
use App\Core\Shared\Exception\File\UnableToDecodeJSONException;
use App\Core\Shared\Exception\File\UnableToReadFileException;
use App\Core\Shared\Ports\Config\ConfigInstancePort;
use App\Core\Shared\Ports\IO\File\ReadJsonFileNotifyPort;

final readonly class ReadOutboundTemplate
{
    public function __construct(
        private ReadJsonFileNotifyPort $readJsonFileNotifyPort,
        private ConfigInstancePort     $configInstancePort,
    )
    {
    }

    /**
     * Reads outbound template from file
     *
     * @return array Outbound template as json decoded array
     *
     * @throws CriticalException If unable to read file, parse json or format is invalid
     */
    public function read(): array
    {
        try {
            return $this->readJsonFileNotifyPort
                ->notifyStartAndSuccess(
                    "Reading outbound template file...",
                    "Outbound template file successfully read"
                )->read($this->configInstancePort->get()->singBoxConfig->templates->outbound);
        } catch (UnableToDecodeJSONException|UnableToReadFileException $e) {
            throw new CriticalException(
                ($e instanceof UnableToDecodeJSONException)
                    ? "Unable to parse JSON from outbound template file"
                    : "Unable to read outbound template file",
                $this->configInstancePort->get()->singBoxConfig->templates->outbound
            );
        }
    }
}