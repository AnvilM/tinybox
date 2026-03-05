<?php

declare(strict_types=1);

namespace Application\Providers\App\Shared;

use App\Core\Shared\Ports\Config\ConfigInstancePort;
use App\Core\Shared\Ports\Http\HttpProt;
use App\Core\Shared\Ports\IO\Directory\ScanDirectoryForFilesPort;
use App\Core\Shared\Ports\IO\File\ReadFilePort;
use App\Core\Shared\Ports\IO\File\ReadJsonFileNotifyPort;
use App\Core\Shared\Ports\IO\File\ReadJsonFilePort;
use App\Core\Shared\Ports\IO\File\SaveFileNotifyPort;
use App\Core\Shared\Ports\IO\File\SaveFilePort;
use App\Core\Shared\Ports\IO\Reporter\ReporterPort;
use App\Core\Shared\Ports\OS\Directories\GetConfigsDirectoryPort;
use App\Core\Shared\Ports\OS\Directories\GetDataHomeDirectoryPort;
use App\Core\Shared\Ports\OS\Path\NormalizePathPort;
use App\Infrastructure\Config\Instance\ConfigInstance;
use App\Infrastructure\IO\File\ReadJsonFile;
use App\Infrastructure\IO\File\ReadJsonFileNotify;
use App\Infrastructure\IO\File\SaveFileNotify;
use App\Infrastructure\Shared\Http\Http;
use App\Infrastructure\Shared\IO\Directory\ScanDirectoryForFiles;
use App\Infrastructure\Shared\IO\File\ReadFile;
use App\Infrastructure\Shared\IO\File\SaveFile;
use App\Infrastructure\Shared\IO\Reporter\Reporter;
use App\Infrastructure\Shared\OS\Directories\GetConfigsDirectory;
use App\Infrastructure\Shared\OS\Directories\GetDataHomeDirectory;
use App\Infrastructure\Shared\OS\Path\NormalizePath;
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
            ConfigInstancePort::class => autowire(ConfigInstance::class),
            ReadJsonFilePort::class => autowire(ReadJsonFile::class),
            ReadJsonFileNotifyPort::class => autowire(ReadJsonFileNotify::class),
            ScanDirectoryForFilesPort::class => autowire(ScanDirectoryForFiles::class),
            SaveFileNotifyPort::class => autowire(SaveFileNotify::class),
            NormalizePathPort::class => autowire(NormalizePath::class),
            GetConfigsDirectoryPort::class => autowire(GetConfigsDirectory::class),
            GetDataHomeDirectoryPort::class => autowire(GetDataHomeDirectory::class),
        ];
    }
}