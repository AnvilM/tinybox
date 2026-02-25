<?php

declare(strict_types=1);

namespace App\Core\Shared\ReporterEvent\Events\Shared\File;

use App\Core\Shared\ReporterEvent\ReporterEvent;
use App\Core\Shared\VO\ReporterEvent\DebugMessagesVO;
use App\Core\Shared\VO\ReporterEvent\TypeVO;

final readonly class FileSavingSuccessfullyReporterEvent extends ReporterEvent
{
    public function __construct(string $fileTitle, string $path)
    {
        parent::__construct(
            "File $fileTitle successfully saved",
            TypeVO::Success,
            DebugMessagesVO::create([
                "Saved to $path",
            ])
        );
    }
}