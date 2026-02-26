<?php

declare(strict_types=1);

namespace App\Infrastructure\Shared\File;

use App\Core\Shared\Exception\File\UnableToDecodeJSONException;
use App\Core\Shared\Exception\File\UnableToReadFileException;
use App\Core\Shared\Ports\File\JsonReaderPort;
use JsonException;

final readonly class JsonReader implements JsonReaderPort
{

    public function read(string $path): array
    {

        $fileRawContent = @file_get_contents($path);

        if ($fileRawContent === false) throw new UnableToReadFileException();

        try {
            $fileContent = json_decode($fileRawContent, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            throw new UnableToDecodeJSONException();
        }

        return $fileContent;
    }
}