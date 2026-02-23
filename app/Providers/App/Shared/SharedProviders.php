<?php

declare(strict_types=1);

namespace Application\Providers\App\Shared;

use App\Core\Shared\Ports\Config\Application\ApplicationConfigPort;
use App\Core\Shared\Ports\Config\Subscription\SubscriptionConfigPort;
use App\Core\Shared\Ports\File\JsonReaderPort;
use App\Core\Shared\Ports\Http\HttpProt;
use App\Core\Shared\Ports\Output\OutputPort;
use App\Core\Shared\Ports\Reporter\ReporterPort;
use App\Infrastructure\Shared\CLI\Output;
use App\Infrastructure\Shared\Config\ApplicationConfig;
use App\Infrastructure\Shared\Config\SubscriptionConfig;
use App\Infrastructure\Shared\File\JsonReader;
use App\Infrastructure\Shared\Http\Http;
use App\Infrastructure\Shared\Reporter\Reporter;
use Application\Providers\ProviderInterface;
use function DI\autowire;

final readonly class SharedProviders implements ProviderInterface
{
    public static function register(): array
    {
        return [
            ApplicationConfigPort::class => autowire(ApplicationConfig::class),
            SubscriptionConfigPort::class => autowire(SubscriptionConfig::class),
            JsonReaderPort::class => autowire(JsonReader::class),
            HttpProt::class => autowire(Http::class),
            OutputPort::class => autowire(Output::class),
            ReporterPort::class => autowire(Reporter::class),
        ];
    }
}