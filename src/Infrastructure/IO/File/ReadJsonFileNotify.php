<?php

declare(strict_types=1);

namespace App\Infrastructure\IO\File;

use App\Domain\Shared\Ports\IO\File\ReadJsonFileNotifyPort;
use App\Domain\Shared\Ports\IO\File\ReadJsonFilePort;
use App\Domain\Shared\Ports\IO\Reporter\ReporterInstancePort;
use App\Domain\Shared\ReporterEvent\ReporterEventBuilder;
use App\Domain\Shared\VO\ReporterEvent\ReporterEventAttachmentVO;

final class ReadJsonFileNotify implements ReadJsonFileNotifyPort
{
    private ?string $notifyStartReading = null;
    private ?string $notifyReadSuccessfully = null;

    public function __construct(
        private readonly ReadJsonFilePort     $readJsonFilePort,
        private readonly ReporterInstancePort $reporterInstancePort,
    )
    {
    }

    public function read(string $path): array
    {
        /**
         * Notify start file reading
         */
        if ($this->notifyStartReading)
            $this->reporterInstancePort->get()->notify(
                ReporterEventBuilder::step($this->notifyStartReading)->attachments(
                    ReporterEventAttachmentVO::veryVerbose('Path: ' . $path)
                )->verbose()
            );


        /**
         * Reading file content
         */
        $fileContent = $this->readJsonFilePort->read($path);


        /**
         * Notify file reading successfully
         */
        if ($this->notifyReadSuccessfully)
            $this->reporterInstancePort->get()->notify(
                ReporterEventBuilder::success($this->notifyReadSuccessfully)->attachments(
                    ReporterEventAttachmentVO::veryVerbose('Path: ' . $path)
                )->verbose()
            );

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