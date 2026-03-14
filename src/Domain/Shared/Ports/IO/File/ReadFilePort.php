<?php

declare(strict_types=1);

namespace App\Domain\Shared\Ports\IO\File;

use App\Domain\Shared\Exception\File\UnableToReadFileException;

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