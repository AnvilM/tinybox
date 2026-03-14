<?php

declare(strict_types=1);

namespace App\Domain\Shared\ReporterEvent\Events\AddScheme\Handler\AddSchemeHandler;

use App\Domain\Shared\ReporterEvent\ReporterEvent;
use App\Domain\Shared\VO\ReporterEvent\ReporterEventDebugMessagesVO;
use App\Domain\Shared\VO\ReporterEvent\ReporterEventTypeVO;

final readonly class InvalidSchemeReporterEvent extends ReporterEvent
{
    public function __construct(string $scheme)
    {
        parent::__construct("Invalid scheme", ReporterEventTypeVO::Skipped, new ReporterEventDebugMessagesVO([$scheme]));
    }
}