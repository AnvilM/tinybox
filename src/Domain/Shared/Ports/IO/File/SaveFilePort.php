<?php

declare(strict_types=1);

namespace App\Domain\Shared\Ports\IO\File;

use App\Domain\Shared\Exception\File\UnableToSaveFileException;

interface SaveFilePort
{
    /**
     * Save string to file
     *
     * @param string $path Path to file
     * @param string $fileContent Content to save in file
     *
     * @return void
     *
     * @throws UnableToSaveFileException Throws if unable to save file
     */
    public function save(string $path, string $fileContent): void;
}