<?php

declare(strict_types=1);

namespace App\Infrastructure\IO\File;

use App\Domain\Shared\Exception\Json\UnableToDecodeJsonException;
use App\Domain\Shared\Ports\IO\File\ReadJsonFilePort;
use App\Infrastructure\Shared\IO\File\ReadFile;
use JsonException;

final readonly class ReadJsonFile implements ReadJsonFilePort
{
    public function __construct(
        private ReadFile $readFilePort,
    )
    {
    }

    public function read(string $path): array
    {
        try {


            /**
             * Read file content
             */
            $rawContent = $this->readFilePort->read($path);

            /**
             * Return empty array if file is empty
             */
            if (trim($rawContent) === '') return [];

            /**
             * Try to decode JSON if file isn't empty
             */
            return json_decode($this->readFilePort->read($path), true, 512, JSON_THROW_ON_ERROR);


        } catch (JsonException) {

            /**
             * Throw exception if unable to decode file content
             */
            throw new UnableToDecodeJsonException($path);
        }
    }
}