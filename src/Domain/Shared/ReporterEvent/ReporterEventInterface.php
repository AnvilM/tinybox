<?php

declare(strict_types=1);

namespace App\Domain\Shared\ReporterEvent;

use App\Domain\Shared\VO\ReporterEvent\ReporterEventAttachmentVO;
use App\Domain\Shared\VO\ReporterEvent\ReporterEventTypeVO;
use App\Domain\Shared\VO\ReporterEvent\ReporterEventVerbosity;

interface ReporterEventInterface
{
    public function getMessage(): string;

    public function getType(): ReporterEventTypeVO;

    public function getVerbosity(): ReporterEventVerbosity;

    /**
     * @return ReporterEventAttachmentVO[]
     */
    public function getAttachments(): array;
}