<?php

declare(strict_types=1);

namespace App\Core\Shared\ReporterEvent\Events\Shared\File;

use App\Core\Shared\ReporterEvent\ReporterEvent;
use App\Core\Shared\VO\ReporterEvent\DebugMessagesVO;
use App\Core\Shared\VO\ReporterEvent\TypeVO;

final readonly class StartSavingFileReporterEvent extends ReporterEvent
{
    public function __construct(string $fileTitle, string $filePath)
    {
        parent::__construct(
            "Saving $fileTitle file...",
            TypeVO::Step,
            DebugMessagesVO::create([
                "Saving to $filePath",
            ])
        );
    }
}