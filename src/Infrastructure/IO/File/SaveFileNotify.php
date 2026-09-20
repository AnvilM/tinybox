<?php

declare(strict_types=1);

namespace App\Infrastructure\IO\File;

use App\Domain\Shared\Ports\IO\File\SaveFileNotifyPort;
use App\Domain\Shared\Ports\IO\File\SaveFilePort;
use App\Domain\Shared\Ports\IO\Reporter\ReporterInstancePort;
use App\Domain\Shared\ReporterEvent\ReporterEventBuilder;
use App\Domain\Shared\VO\ReporterEvent\ReporterEventAttachmentVO;

final class SaveFileNotify implements SaveFileNotifyPort
{
    private ?string $notifyStartSaving = null;
    private ?string $notifySavedSuccessfully = null;

    public function __construct(
        private readonly SaveFilePort         $saveFilePort,
        private readonly ReporterInstancePort $reporterInstancePort,
    )
    {
    }

    public function save(string $path, string $fileContent): void
    {
        /**
         * Notify start file saving
         */
        if ($this->notifyStartSaving)
            $this->reporterInstancePort->get()->notify(
                ReporterEventBuilder::step($this->notifyStartSaving)->attachments(
                    ReporterEventAttachmentVO::veryVerbose('Path: ' . $path)
                )->verbose()
            );

        /**
         * Save file
         */
        $this->saveFilePort->save($path, $fileContent);


        /**
         * Notify file saved successfully
         */
        if ($this->notifySavedSuccessfully)
            $this->reporterInstancePort->get()->notify(
                ReporterEventBuilder::success($this->notifySavedSuccessfully)->attachments(
                    ReporterEventAttachmentVO::veryVerbose('Path: ' . $path)
                )->verbose()
            );
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