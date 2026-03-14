<?php

declare(strict_types=1);

namespace App\Domain\Shared\Ports\IO\File;

use App\Domain\Shared\Exception\File\UnableToReadFileException;
use App\Domain\Shared\Exception\Json\UnableToDecodeJsonException;

interface ReadJsonFilePort
{
    /**
     * Read file and decode json to array
     *
     * @param string $path Path to file
     *
     * @return array Json decoded array
     *
     * @throws UnableToReadFileException Throws if unable to read file
     * @throws UnableToDecodeJsonException Throws if unable to decode json
     */
    public function read(string $path): array;
}