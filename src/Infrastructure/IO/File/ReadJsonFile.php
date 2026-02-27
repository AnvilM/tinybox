<?php

declare(strict_types=1);

namespace App\Infrastructure\IO\File;

use App\Core\Shared\Exception\File\UnableToDecodeJSONException;
use App\Core\Shared\Ports\IO\File\ReadJsonFilePort;
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
            return json_decode($this->readFilePort->read($path), true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            throw new UnableToDecodeJSONException();
        }
    }
}