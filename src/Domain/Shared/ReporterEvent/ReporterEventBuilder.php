<?php

declare(strict_types=1);

namespace App\Domain\Shared\ReporterEvent;

use App\Domain\Shared\VO\ReporterEvent\ReporterEventAttachmentVO;
use App\Domain\Shared\VO\ReporterEvent\ReporterEventTypeVO;
use App\Domain\Shared\VO\ReporterEvent\ReporterEventVerbosity;

final class ReporterEventBuilder
{

    private array $attachments = [];

    public function __construct(
        private readonly string              $message,
        private readonly ReporterEventTypeVO $type
    )
    {
    }


    public static function error(string $message): self
    {
        return new self($message, ReporterEventTypeVO::Error);
    }

    public static function warning(string $message): self
    {
        return new self($message, ReporterEventTypeVO::Warning);
    }

    public static function success(string $message): self
    {
        return new self($message, ReporterEventTypeVO::Success);
    }

    public static function skipped(string $message): self
    {
        return new self($message, ReporterEventTypeVO::Skipped);
    }

    public static function step(string $message): self
    {
        return new self($message, ReporterEventTypeVO::Step);
    }


    public function quite(): ReporterEvent
    {
        return new ReporterEvent($this->message, $this->type, ReporterEventVerbosity::Quiet, ...$this->attachments);
    }

    public function normal(): ReporterEvent
    {
        return new ReporterEvent($this->message, $this->type, ReporterEventVerbosity::Normal, ...$this->attachments);
    }

    public function verbose(): ReporterEvent
    {
        return new ReporterEvent($this->message, $this->type, ReporterEventVerbosity::Verbose, ...$this->attachments);
    }

    public function veryVerbose(): ReporterEvent
    {
        return new ReporterEvent($this->message, $this->type, ReporterEventVerbosity::VeryVerbose, ...$this->attachments);
    }

    public function debug(): ReporterEvent
    {
        return new ReporterEvent($this->message, $this->type, ReporterEventVerbosity::Debug, ...$this->attachments);
    }

    public function attachments(ReporterEventAttachmentVO ...$attachments): self
    {
        $this->attachments = array_merge($this->attachments, $attachments);

        return $this;
    }
}