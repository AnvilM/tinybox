<?php

declare(strict_types=1);

namespace App\Infrastructure\IO\File;

use App\Domain\Shared\Ports\IO\File\SaveFileNotifyPort;
use App\Domain\Shared\Ports\IO\File\SaveFilePort;
use App\Domain\Shared\Ports\IO\Reporter\ReporterPort;
use App\Domain\Shared\ReporterEvent\Events\Shared\IO\File\FileReadingStartReporterEvent;
use App\Domain\Shared\ReporterEvent\Events\Shared\IO\File\FileReadSuccessfullyReporterEvent;

final class SaveFileNotify implements SaveFileNotifyPort
{
    private ?string $notifyStartSaving = null;
    private ?string $notifySavedSuccessfully = null;

    public function __construct(
        private readonly SaveFilePort $saveFilePort,
        private readonly ReporterPort $reporterPort,
    )
    {
    }

    public function save(string $path, string $fileContent): void
    {
        /**
         * Notify start file saving
         */
        if ($this->notifyStartSaving)
            $this->reporterPort->notify(new FileReadingStartReporterEvent($this->notifyStartSaving, $path));


        /**
         * Save file
         */
        $this->saveFilePort->save($path, $fileContent);


        /**
         * Notify file saved successfully
         */
        if ($this->notifySavedSuccessfully)
            $this->reporterPort->notify(new FileReadSuccessfullyReporterEvent($this->notifySavedSuccessfully, $path));
    }

    public function notifyStartAndSuccess(string $startMessage, string $successMessage): self
    {
        return $this->notifyStartSaving($startMessage)
            ->notifySavedSuccessfully($successMessage);
    }

    public function notifySavedSuccessfully(string $message): self
    {
        $this->notifySavedSuccessfully = $message;

        return $this;
    }

    public function notifyStartSaving(string $message): self
    {
        $this->notifyStartSaving = $message;

        return $this;
    }
}