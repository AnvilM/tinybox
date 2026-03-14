<?php

declare(strict_types=1);

namespace App\Infrastructure\Shared\OS\Directories;

use App\Domain\Shared\Exception\CriticalException;
use App\Domain\Shared\Ports\OS\Directories\GetConfigsDirectoryPort;
use Application\Config\ApplicationConfig\ApplicationConfig;
use RuntimeException;
use UnexpectedValueException;

final readonly class GetConfigsDirectory implements GetConfigsDirectoryPort
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
        if (getenv('HOME') === false) {
            throw new RuntimeException("Cannot get HOME environment variable");
        }


        if (!is_string(getenv('HOME'))) {
            throw new UnexpectedValueException("HOME environment variable must be a string");
        }


        return getenv('HOME') . '/.config/' . ApplicationConfig::appName . '/';
    }
}