<?php

declare(strict_types=1);

namespace Application\Config\LoggerConfig;

use Application\Config\ApplicationConfig\ApplicationConfig;
use Application\Config\ApplicationConfig\ApplicationEnvironmentEnum;
use Monolog\Level;

final readonly class LoggerConfig
{
    public static function level(): Level
    {
        return getopt('d', ['debug']) !== null ? Level::Debug : Level::Info;
    }
}