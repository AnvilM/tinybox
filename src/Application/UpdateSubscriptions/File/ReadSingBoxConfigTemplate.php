<?php

declare(strict_types=1);

namespace App\Application\UpdateSubscriptions\File;

use App\Core\Shared\Exception\CriticalException;
use App\Core\Shared\Exception\File\UnableToDecodeJSONException;
use App\Core\Shared\Exception\File\UnableToReadFileException;
use App\Core\Shared\Ports\Config\ConfigFactoryPort;
use App\Core\Shared\Ports\File\JsonReaderPort;

final readonly class ReadSingBoxConfigTemplate
{
    public function __construct(
        private JsonReaderPort    $jsonReaderPort,
        private ConfigFactoryPort $configFactoryPort,
    )
    {
    }

    /**
     * Reads sing-box config template from file
     *
     * @return array Sing-box config template as json decoded array
     *
     * @throws CriticalException If unable to read file, parse json or format is invalid
     */
    public function read(): array
    {
        try {
            return $this->jsonReaderPort->read(
                $this->configFactoryPort->get()->singBoxConfig->templates->config,
            );
        } catch (UnableToDecodeJSONException|UnableToReadFileException $e) {
            throw new CriticalException(
                ($e instanceof UnableToDecodeJSONException)
                    ? "Unable to parse JSON from sing-box config template file"
                    : "Unable to read sing-box config template file",
                $this->configFactoryPort->get()->singBoxConfig->templates->config
            );
        }
    }
}