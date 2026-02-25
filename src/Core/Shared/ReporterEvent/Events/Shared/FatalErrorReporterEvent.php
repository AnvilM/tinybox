<?php

declare(strict_types=1);

namespace App\Core\Shared\ReporterEvent\Events\Shared;

use App\Core\Shared\ReporterEvent\ReporterEvent;
use App\Core\Shared\VO\ReporterEvent\BreadcrumbsVO;
use App\Core\Shared\VO\ReporterEvent\DebugMessagesVO;
use App\Core\Shared\VO\ReporterEvent\TypeVO;

final readonly class FatalErrorReporterEvent extends ReporterEvent
{
    public function __construct(string $message, ?DebugMessagesVO $debugMessagesVO = null, ?BreadcrumbsVO $breadcrumbsVO = null)
    {
        parent::__construct($message, TypeVO::Error, $debugMessagesVO, $breadcrumbsVO);
    }
}