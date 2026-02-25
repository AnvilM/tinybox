<?php

declare(strict_types=1);

namespace App\Infrastructure\Shared\Config;

use App\Core\Shared\Exception\CriticalException;
use App\Core\Shared\Ports\Config\SingBox\SingBoxConfigPort;
use Application\Config\SingBoxConfig\SingBoxConfig as Config;
use RuntimeException;

final readonly class SingBoxConfig implements SingBoxConfigPort
{
    public static function singBoxOutboundTemplatePath(): string
    {
        try {
            return Config::singBoxOutboundTemplatePath();
        } catch (RuntimeException $exception) {
            throw new CriticalException("Unable to find outbound template path", $exception->getMessage());
        }
    }

    public static function singBoxUrltestOutboundTemplatePath(): string
    {
        try {
            return Config::singBoxUrltestOutboundTemplatePath();
        } catch (RuntimeException $exception) {
            throw new CriticalException("Unable to find urltest outbound template path", $exception->getMessage());
        }

    }

    public static function singBoxConfigTemplatePath(): string
    {
        try {
            return Config::singBoxConfigTemplatePath();
        } catch (RuntimeException $exception) {
            throw new CriticalException("Unable to find sing-box config template path", $exception->getMessage());
        }
    }

    public static function singBoxConfigSaveDirectoryPath(): string
    {
        try {
            return Config::singBoxConfigSaveDirectoryPath();
        } catch (RuntimeException $exception) {
            throw new CriticalException("Unable to find sing-box config save directory path", $exception->getMessage());
        }
    }

}