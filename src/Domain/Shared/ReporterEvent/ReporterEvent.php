<?php

declare(strict_types=1);

namespace App\Domain\Shared\ReporterEvent;

use App\Domain\Shared\VO\ReporterEvent\ReporterEventAttachmentVO;
use App\Domain\Shared\VO\ReporterEvent\ReporterEventTypeVO;
use App\Domain\Shared\VO\ReporterEvent\ReporterEventVerbosity;

readonly class ReporterEvent implements ReporterEventInterface
{
    /**
     * @var ReporterEventAttachmentVO[]
     */
    private array $attachments;

    public function __construct(
        private string                  $message,
        private ?ReporterEventTypeVO    $type = null,
        private ?ReporterEventVerbosity $verbosity = null,
        ?ReporterEventAttachmentVO      ...$attachments,
    )
    {
        $normalizedAttachments = [];
        foreach ($attachments as $attachment) {
            if ($attachment->verbosity->value < $this->getVerbosity()) {
                $attachment = new ReporterEventAttachmentVO($attachment->message, $this->getVerbosity());
            }

            $normalizedAttachments[] = $attachment;
        }

        $this->attachments = $normalizedAttachments;
    }

    public function getVerbosity(): ReporterEventVerbosity
    {
        return $this->verbosity ?? ReporterEventVerbosity::Normal;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getType(): ReporterEventTypeVO
    {
        return $this->type ?? ReporterEventTypeVO::Step;
    }

    public function getAttachments(): array
    {
        return $this->attachments;
    }
}