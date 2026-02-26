<?php

declare(strict_types=1);

namespace App\Core\Shared\Ports\File;

use App\Core\Shared\Exception\File\UnableToDecodeJSONException;
use App\Core\Shared\Exception\File\UnableToReadFileException;

interface JsonReaderPort
{
    /**
     * Read and parse json file
     *
     * @param string $path Path to file
     *
     * @return array Json decoded array
     *
     * @throws UnableToReadFileException Throws if unable to read file
     * @throws UnableToDecodeJSONException Throws if unable to decode json
     */
    public function read(string $path): array;
}