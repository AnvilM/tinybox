<?php

declare(strict_types=1);

namespace App\Domain\Shared\ReporterEvent\Events\Shared;

use App\Domain\Shared\ReporterEvent\ReporterEvent;
use App\Domain\Shared\VO\ReporterEvent\ReporterEventBreadcrumbsVO;
use App\Domain\Shared\VO\ReporterEvent\ReporterEventDebugMessagesVO;
use App\Domain\Shared\VO\ReporterEvent\ReporterEventTypeVO;

final readonly class FatalErrorReporterEvent extends ReporterEvent
{
    public function __construct(string $message, ?ReporterEventDebugMessagesVO $debugMessagesVO = null, ?ReporterEventBreadcrumbsVO $breadcrumbsVO = null)
    {
        parent::__construct($message, ReporterEventTypeVO::Error, $debugMessagesVO, $breadcrumbsVO);
    }
}