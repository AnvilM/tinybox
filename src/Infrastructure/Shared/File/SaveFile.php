<?php

declare(strict_types=1);

namespace App\Infrastructure\Shared\File;

use App\Core\Shared\Exception\File\UnableToSaveFileException;
use App\Core\Shared\Ports\File\SaveFilePort;
use App\Core\Shared\Ports\Reporter\ReporterPort;
use App\Core\Shared\ReporterEvent\Events\Shared\File\FileSavingSuccessfullyReporterEvent;
use App\Core\Shared\ReporterEvent\Events\Shared\File\StartSavingFileReporterEvent;

final readonly class SaveFile implements SaveFilePort
{
    public function __construct(
        private ReporterPort $reporterPort,
    )
    {
    }

    public function save(string $path, string $fileContent, ?string $fileTitle = null, bool $notifyStartSaving = false, bool $notifySavingSuccessfully = false): void
    {
        if ($notifyStartSaving && $fileTitle)
            $this->reporterPort->notify(new StartSavingFileReporterEvent(
                $fileTitle, $path
            ));


        $fileSavingResult = @file_put_contents($path, $fileContent);

        if ($fileSavingResult === false) throw new UnableToSaveFileException();

        if ($notifySavingSuccessfully && $fileTitle)
            $this->reporterPort->notify(new FileSavingSuccessfullyReporterEvent(
                $fileTitle, $path
            ));
    }


}