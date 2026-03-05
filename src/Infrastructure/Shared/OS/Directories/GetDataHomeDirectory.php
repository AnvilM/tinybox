<?php

declare(strict_types=1);

namespace App\Infrastructure\Shared\OS\Directories;

use App\Core\Shared\Exception\CriticalException;
use App\Core\Shared\Ports\OS\Directories\GetDataHomeDirectoryPort;
use Application\Config\ApplicationConfig\ApplicationConfig;
use RuntimeException;

final readonly class GetDataHomeDirectory implements GetDataHomeDirectoryPort
{
    public function execute(): string
    {
        return match (PHP_OS_FAMILY) {
            'Linux' => $this->executeLinux(),
            default => throw new CriticalException("Unsupported OS: " . PHP_OS_FAMILY)
        };
    }

    private function executeLinux(): string
    {
        if (getenv('HOME') === false) throw new RuntimeException("Cannot get HOME environment variable");

        if (!is_string(getenv('HOME'))) throw new RuntimeException("HOME environment variable must be a string");

        return getenv('HOME') . '/.local/share/' . ApplicationConfig::appName . '/';
    }
}