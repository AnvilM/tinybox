<?php

declare(strict_types=1);

namespace App\Infrastructure\Shared\Config;

use App\Core\Shared\Ports\Config\SingBox\SingBoxConfigPort;
use Application\Config\SingBoxConfig\SingBoxConfig as Config;

final readonly class SingBoxConfig implements SingBoxConfigPort
{
    public static function singBoxOutboundTemplatePath(): string
    {
        return Config::singBoxOutboundTemplatePath();
    }

    public static function singBoxUrltestOutboundTemplatePath(): string
    {
        return Config::singBoxUrltestOutboundTemplatePath();
    }

    public static function singBoxConfigTemplatePath(): string
    {
        return Config::singBoxConfigTemplatePath();
    }

    public static function singBoxConfigSaveDirectoryPath(): string
    {
        return Config::singBoxConfigSaveDirectoryPath();
    }

}