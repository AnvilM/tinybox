<?php

declare(strict_types=1);

namespace App\Domain\Shared\ReporterEvent;

use App\Domain\Shared\VO\ReporterEvent\ReporterEventBreadcrumbsVO;
use App\Domain\Shared\VO\ReporterEvent\ReporterEventDebugMessagesVO;
use App\Domain\Shared\VO\ReporterEvent\ReporterEventTypeVO;

interface ReporterEventInterface
{
    public function getMessage(): string;

    public function getType(): ReporterEventTypeVO;

    public function getDebugMessage(): ?ReporterEventDebugMessagesVO;

    public function getBreadcrumbsVO(): ?ReporterEventBreadcrumbsVO;
}