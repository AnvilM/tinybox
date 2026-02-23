<?php

declare(strict_types=1);

namespace App\Core\Shared\Ports\File;

use App\Core\Shared\Exception\CriticalException;

interface JsonReaderPort
{
    /**
     * Read and parse json file
     *
     * @param string $path Path to file
     * @param string $fileTitle File title to print in output
     * @param string|null $successMessage Message to print in success
     *
     * @return array Json decoded array
     *
     * @throws CriticalException Throws if unable to read file or parse json
     */
    public function read(string $path, string $fileTitle, ?string $successMessage = null): array;
}