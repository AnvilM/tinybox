<?php

declare(strict_types=1);

namespace App\Infrastructure\Shared\Config;

use App\Core\Shared\Ports\Config\Application\ApplicationConfigPort;
use Application\Config\ApplicationConfig\ApplicationConfig as Config;

final readonly class ApplicationConfig implements ApplicationConfigPort
{

    public static function isDebug(): bool
    {
        return Config::isDebug();
    }
}