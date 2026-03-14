<?php

declare(strict_types=1);

namespace App\Infrastructure\Shared\IO\Directory;

use App\Domain\Shared\Ports\IO\Directory\ScanDirectoryForFilesPort;
use FilesystemIterator;
use InvalidArgumentException;
use RuntimeException;

final readonly class ScanDirectoryForFiles implements ScanDirectoryForFilesPort
{
    public function scan(string $path): array
    {
        if (!is_dir($path)) {
            throw new InvalidArgumentException("Path '$path' is not a valid directory");
        }

        if (!is_readable($path)) {
            throw new RuntimeException("Directory '$path' is not readable");
        }

        $files = [];

        $iterator = new FilesystemIterator(
            $path,
            FilesystemIterator::SKIP_DOTS | FilesystemIterator::CURRENT_AS_FILEINFO
        );

        foreach ($iterator as $fileInfo) {
            if ($fileInfo->isFile()) {
                $files[] = $fileInfo->getFilename();
            }
        }

        return $files;
    }
}