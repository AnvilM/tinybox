<?php

declare(strict_types=1);

namespace App\Domain\Shared\ReporterEvent\Events\Shared\IO\File;

use App\Domain\Shared\ReporterEvent\ReporterEvent;
use App\Domain\Shared\VO\ReporterEvent\ReporterEventDebugMessagesVO;
use App\Domain\Shared\VO\ReporterEvent\ReporterEventTypeVO;

final readonly class FileReadSuccessfullyReporterEvent extends ReporterEvent
{
    public function __construct(string $message, string $filePath)
    {
        parent::__construct(
            $message,
            ReporterEventTypeVO::Success,
            new ReporterEventDebugMessagesVO([$filePath])
        );
    }
}