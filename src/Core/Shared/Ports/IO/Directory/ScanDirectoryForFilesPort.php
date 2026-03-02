<?php

declare(strict_types=1);

namespace App\Core\Shared\Ports\IO\Directory;

use InvalidArgumentException;
use RuntimeException;

interface ScanDirectoryForFilesPort
{
    /**
     * Scan directory for files in provided directory NOT recursively and returns array of files names
     *
     * @param string $path Path to directory
     *
     * @return array Array of files in directory excluded . and .., e.g., ["file1", "file2", ...]
     *
     * @throws InvalidArgumentException If path does not exist or is not a directory.
     * @throws RuntimeException If directory is not readable.
     */
    public function scan(string $path): array;
}