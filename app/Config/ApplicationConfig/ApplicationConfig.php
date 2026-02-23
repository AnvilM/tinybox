<?php

declare(strict_types=1);

namespace Application\Config\ApplicationConfig;

use RuntimeException;

final readonly class ApplicationConfig
{
    public const string appName = "tinybox";

    public static function isDebug(): bool
    {
        $argv = $_SERVER['argv'] ?? [];

        foreach ($argv as $arg) {
            if ($arg === '-d' || $arg === '--debug') {
                return true;
            }
        }

        return false;
    }

    /**
     * @throws RuntimeException
     */
    public static function configDirectoryPath(): string
    {
        if (getenv('HOME') === false) throw new RuntimeException("Cannot get HOME environment variable");

        if (!is_string(getenv('HOME'))) throw new RuntimeException("HOME environment variable must be a string");

        return getenv('HOME') . '/.config/' . self::appName . '/';
    }

    /**
     * @throws RuntimeException
     */
    public static function dataHomeDirectoryPath(): string
    {
        if (getenv('HOME') === false) throw new RuntimeException("Cannot get HOME environment variable");

        if (!is_string(getenv('HOME'))) throw new RuntimeException("HOME environment variable must be a string");

        return getenv('HOME') . '/.local/share/' . self::appName . '/';
    }


}