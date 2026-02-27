<?php

declare(strict_types=1);

namespace App\Infrastructure\Shared\OS\Helper;

use App\Infrastructure\Shared\OS\AbstractOSHelper;
use Application\Config\ApplicationConfig\ApplicationConfig;
use RuntimeException;

final readonly class GetConfigsDirectory extends AbstractOSHelper
{
    protected function executeLinux(...$args): string
    {
        if (getenv('HOME') === false) throw new RuntimeException("Cannot get HOME environment variable");

        if (!is_string(getenv('HOME'))) throw new RuntimeException("HOME environment variable must be a string");

        return getenv('HOME') . '/.config/' . ApplicationConfig::appName . '/';
    }
}