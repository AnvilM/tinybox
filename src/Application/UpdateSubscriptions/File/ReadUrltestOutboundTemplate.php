<?php

declare(strict_types=1);

namespace App\Application\UpdateSubscriptions\File;

use App\Core\Shared\Exception\CriticalException;
use App\Core\Shared\Exception\File\UnableToDecodeJSONException;
use App\Core\Shared\Exception\File\UnableToReadFileException;
use App\Core\Shared\Ports\Config\SingBox\SingBoxConfigPort;
use App\Core\Shared\Ports\File\JsonReaderPort;

final readonly class ReadUrltestOutboundTemplate
{
    public function __construct(
        private JsonReaderPort    $jsonReaderPort,
        private SingBoxConfigPort $singBoxConfigPort,
    )
    {
    }

    /**
     * Reads urltest outbound template from file
     *
     * @return array Urltest outbound template as json decoded array
     *
     * @throws CriticalException If unable to read file, parse json or format is invalid
     */
    public function read(): array
    {
        try {
            return $this->jsonReaderPort->read(
                $this->singBoxConfigPort::singBoxUrltestOutboundTemplatePath(),
                "urltest outbound template"
            );
        } catch (UnableToDecodeJSONException|UnableToReadFileException $e) {
            throw new CriticalException(
                ($e instanceof UnableToDecodeJSONException)
                    ? "Unable to parse JSON from urltest outbound template file"
                    : "Unable to read urltest outbound template file",
                $this->singBoxConfigPort::singBoxOutboundTemplatePath()
            );
        }
    }
}