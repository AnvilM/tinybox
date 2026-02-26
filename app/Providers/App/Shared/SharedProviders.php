<?php

declare(strict_types=1);

namespace Application\Providers\App\Shared;

use App\Core\Shared\Ports\Config\ConfigFactoryPort;
use App\Core\Shared\Ports\File\JsonReaderPort;
use App\Core\Shared\Ports\File\SaveFilePort;
use App\Core\Shared\Ports\Http\HttpProt;
use App\Core\Shared\Ports\Reporter\ReporterPort;
use App\Infrastructure\Config\Factory\ConfigFactory;
use App\Infrastructure\Shared\File\JsonReader;
use App\Infrastructure\Shared\File\SaveFile;
use App\Infrastructure\Shared\Http\Http;
use App\Infrastructure\Shared\Reporter\Reporter;
use Application\Providers\ProviderInterface;
use function DI\autowire;

final readonly class SharedProviders implements ProviderInterface
{
    public static function register(): array
    {
        return [
            JsonReaderPort::class => autowire(JsonReader::class),
            HttpProt::class => autowire(Http::class),
            ReporterPort::class => autowire(Reporter::class),
            SaveFilePort::class => autowire(SaveFile::class),
            ConfigFactoryPort::class => autowire(ConfigFactory::class),
        ];
    }
}