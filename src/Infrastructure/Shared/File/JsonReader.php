<?php

declare(strict_types=1);

namespace App\Infrastructure\Shared\File;

use App\Core\Shared\Exception\CriticalException;
use App\Core\Shared\Ports\File\JsonReaderPort;
use App\Core\Shared\Ports\Reporter\ReporterPort;
use App\Core\Shared\ReporterEvent\Events\Shared\File\FileReadSuccessfullyReporterEvent;
use App\Core\Shared\ReporterEvent\Events\Shared\File\StartReadingFileReporterEvent;
use JsonException;

final readonly class JsonReader implements JsonReaderPort
{
    public function __construct(
        private ReporterPort $reporterPort,
    )
    {
    }

    public function read(string $path, string $fileTitle, ?string $successMessage = null): array
    {

        $this->reporterPort->notify(new StartReadingFileReporterEvent(
            $fileTitle, $path
        ));


        $fileRawContent = @file_get_contents($path);

        if ($fileRawContent === false) throw new CriticalException("Unable to read $fileTitle file at $path");

        try {
            $fileContent = json_decode($fileRawContent, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            throw new CriticalException("Unable to parse JSON at $path");
        }

        $this->reporterPort->notify(new FileReadSuccessfullyReporterEvent(
            $fileTitle, $fileRawContent
        ));

        return $fileContent;
    }
}