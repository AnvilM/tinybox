<?php

declare(strict_types=1);

namespace App\Core\Shared\Ports\IO\File;

use App\Core\Shared\Exception\File\UnableToReadFileException;

interface ReadFilePort
{
    /**
     * Read file
     *
     * @param string $path Path to file
     *
     * @return string File content
     *
     * @throws UnableToReadFileException Throws if unable to read file
     */
    public function read(string $path): string;
}