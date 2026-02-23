<?php

declare(strict_types=1);

namespace App\Core\Shared\ReporterEvent\Events\Shared\File;

use App\Core\Shared\ReporterEvent\ReporterEvent;
use App\Core\Shared\VO\ReporterEvent\DebugMessagesVO;
use App\Core\Shared\VO\ReporterEvent\TypeVO;

final readonly class StartReadingFileReporterEvent extends ReporterEvent
{
    public function __construct(string $fileTitle, string $filePath)
    {
        parent::__construct(
            "Reading $fileTitle file...",
            TypeVO::Step,
            DebugMessagesVO::create([
                "Loading from $filePath",
            ])
        );
    }
}