<?php

declare(strict_types=1);

namespace App\Infrastructure\IO\File;

use App\Core\Shared\Ports\IO\File\ReadJsonFileNotifyPort;
use App\Core\Shared\Ports\IO\File\ReadJsonFilePort;
use App\Core\Shared\Ports\IO\Reporter\ReporterPort;
use App\Core\Shared\ReporterEvent\Events\Shared\IO\File\FileReadingStartReporterEvent;
use App\Core\Shared\ReporterEvent\Events\Shared\IO\File\FileReadSuccessfullyReporterEvent;

final class ReadJsonFileNotify implements ReadJsonFileNotifyPort
{
    private ?string $notifyStartReading = null;
    private ?string $notifyReadSuccessfully = null;

    public function __construct(
        private readonly ReadJsonFilePort $readJsonFilePort,
        private readonly ReporterPort     $reporterPort,
    )
    {
    }

    public function read(string $path): array
    {
        /**
         * Notify start file reading
         */
        if ($this->notifyStartReading)
            $this->reporterPort->notify(new FileReadingStartReporterEvent($this->notifyStartReading, $path));


        /**
         * Reading file content
         */
        $fileContent = $this->readJsonFilePort->read($path);


        /**
         * Notify file reading successfully
         */
        if ($this->notifyReadSuccessfully)
            $this->reporterPort->notify(new FileReadSuccessfullyReporterEvent($this->notifyReadSuccessfully, $path));


        return $fileContent;
    }

    public function notifyStartAndSuccess(string $startMessage, string $successMessage): self
    {
        return $this->notifyStartReading($startMessage)
            ->notifyReadSuccessfully($successMessage);
    }

    public function notifyReadSuccessfully(string $message): self
    {
        $this->notifyReadSuccessfully = $message;

        return $this;
    }

    public function notifyStartReading(string $message): self
    {
        $this->notifyStartReading = $message;

        return $this;
    }


}