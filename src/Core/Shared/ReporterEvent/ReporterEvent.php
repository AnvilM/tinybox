<?php

declare(strict_types=1);

namespace App\Core\Shared\ReporterEvent;

use App\Core\Shared\VO\ReporterEvent\BreadcrumbsVO;
use App\Core\Shared\VO\ReporterEvent\DebugMessagesVO;
use App\Core\Shared\VO\ReporterEvent\TypeVO;

abstract readonly class ReporterEvent implements ReporterEventInterface
{
    public function __construct(
        private string           $message,
        private TypeVO           $type,
        private ?DebugMessagesVO $debugMessagesVO = null,
        private ?BreadcrumbsVO   $breadcrumbsVO = null
    )
    {
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getType(): TypeVO
    {
        return $this->type;
    }

    public function getDebugMessage(): ?DebugMessagesVO
    {
        return $this->debugMessagesVO;
    }

    public function getBreadcrumbsVO(): ?BreadcrumbsVO
    {
        return $this->breadcrumbsVO;
    }
}