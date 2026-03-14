<?php

declare(strict_types=1);

namespace App\Domain\Shared\ReporterEvent;

use App\Domain\Shared\VO\ReporterEvent\ReporterEventBreadcrumbsVO;
use App\Domain\Shared\VO\ReporterEvent\ReporterEventDebugMessagesVO;
use App\Domain\Shared\VO\ReporterEvent\ReporterEventTypeVO;

abstract readonly class ReporterEvent implements ReporterEventInterface
{
    public function __construct(
        private string                        $message,
        private ReporterEventTypeVO           $type,
        private ?ReporterEventDebugMessagesVO $debugMessagesVO = null,
        private ?ReporterEventBreadcrumbsVO   $breadcrumbsVO = null
    )
    {
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getType(): ReporterEventTypeVO
    {
        return $this->type;
    }

    public function getDebugMessage(): ?ReporterEventDebugMessagesVO
    {
        return $this->debugMessagesVO;
    }

    public function getBreadcrumbsVO(): ?ReporterEventBreadcrumbsVO
    {
        return $this->breadcrumbsVO;
    }
}