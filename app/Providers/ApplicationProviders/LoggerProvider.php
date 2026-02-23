<?php

declare(strict_types=1);

namespace Application\Providers\ApplicationProviders;

use Application\Config\ApplicationConfig\ApplicationConfig;
use Application\Config\LoggerConfig\LoggerConfig;
use Application\Providers\ProviderInterface;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;

final readonly class LoggerProvider implements ProviderInterface
{

    public static function register(): array
    {
        return [
            LoggerInterface::class => new Logger(ApplicationConfig::appName)
                ->pushHandler(
                    new StreamHandler(
                        'php://stdout',
                        LoggerConfig::level()
                    )
                )
        ];
    }
}