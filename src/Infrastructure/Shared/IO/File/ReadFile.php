<?php

declare(strict_types=1);

namespace App\Infrastructure\Shared\IO\File;

use App\Domain\Shared\Exception\File\UnableToReadFileException;
use App\Domain\Shared\Ports\IO\File\ReadFilePort;

final readonly class ReadFile implements ReadFilePort
{

    public function read(string $path): string
    {
        $fileContent = @file_get_contents($path);

        if ($fileContent === false) throw new UnableToReadFileException($path);

        return $fileContent;
    }
}