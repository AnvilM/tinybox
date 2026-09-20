<?php

declare(strict_types=1);

namespace App\Domain\Shared\VO\ReporterEvent;

final readonly class ReporterEventAttachmentVO
{
    public function __construct(public string                 $message,
                                public ReporterEventVerbosity $verbosity)
    {
    }

    public static function quite(string $message): self
    {
        return new self($message, ReporterEventVerbosity::Quiet);
    }

    public static function normal(string $message): self
    {
        return new self($message, ReporterEventVerbosity::Normal);
    }

    public static function verbose(string $message): self
    {
        return new self($message, ReporterEventVerbosity::Verbose);
    }

    public static function veryVerbose(string $message): self
    {
        return new self($message, ReporterEventVerbosity::VeryVerbose);
    }

    public static function debug(string $message): self
    {
        return new self($message, ReporterEventVerbosity::Debug);
    }


}