<?php

declare(strict_types=1);

namespace Application\Providers\App\Shared;

use App\Core\Shared\Ports\Config\ConfigFactoryPort;
use App\Core\Shared\Ports\Http\HttpProt;
use App\Core\Shared\Ports\IO\Directory\ScanDirectoryForFilesPort;
use App\Core\Shared\Ports\IO\File\ReadFilePort;
use App\Core\Shared\Ports\IO\File\ReadJsonFileNotifyPort;
use App\Core\Shared\Ports\IO\File\ReadJsonFilePort;
use App\Core\Shared\Ports\IO\File\SaveFilePort;
use App\Core\Shared\Ports\IO\Reporter\ReporterPort;
use App\Infrastructure\Config\Factory\ConfigFactory;
use App\Infrastructure\IO\File\ReadJsonFile;
use App\Infrastructure\IO\File\ReadJsonFileNotify;
use App\Infrastructure\Shared\Http\Http;
use App\Infrastructure\Shared\IO\Directory\ScanDirectoryForFiles;
use App\Infrastructure\Shared\IO\File\ReadFile;
use App\Infrastructure\Shared\IO\File\SaveFile;
use App\Infrastructure\Shared\IO\Reporter\Reporter;
use Application\Providers\ProviderInterface;
use function DI\autowire;

final readonly class SharedProviders implements ProviderInterface
{
    public static function register(): array
    {
        return [
            ReadFilePort::class => autowire(ReadFile::class),
            HttpProt::class => autowire(Http::class),
            ReporterPort::class => autowire(Reporter::class),
            SaveFilePort::class => autowire(SaveFile::class),
            ConfigFactoryPort::class => autowire(ConfigFactory::class),
            ReadJsonFilePort::class => autowire(ReadJsonFile::class),
            ReadJsonFileNotifyPort::class => autowire(ReadJsonFileNotify::class),
            ScanDirectoryForFilesPort::class => autowire(ScanDirectoryForFiles::class),
        ];
    }
}