<?php

declare(strict_types=1);

namespace App\Core\Shared\ReporterEvent;

use App\Core\Shared\VO\ReporterEvent\BreadcrumbsVO;
use App\Core\Shared\VO\ReporterEvent\DebugMessagesVO;
use App\Core\Shared\VO\ReporterEvent\TypeVO;

interface ReporterEventInterface
{
    public function getMessage(): string;

    public function getType(): TypeVO;

    public function getDebugMessage(): ?DebugMessagesVO;

    public function getBreadcrumbsVO(): ?BreadcrumbsVO;
}