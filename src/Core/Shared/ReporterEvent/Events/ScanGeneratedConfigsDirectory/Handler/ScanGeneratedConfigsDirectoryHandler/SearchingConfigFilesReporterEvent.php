<?php

declare(strict_types=1);

namespace App\Core\Shared\ReporterEvent\Events\ScanGeneratedConfigsDirectory\Handler\ScanGeneratedConfigsDirectoryHandler;

use App\Core\Shared\ReporterEvent\ReporterEvent;
use App\Core\Shared\VO\ReporterEvent\DebugMessagesVO;
use App\Core\Shared\VO\ReporterEvent\TypeVO;

final readonly class SearchingConfigFilesReporterEvent extends ReporterEvent
{
    public function __construct(string $path)
    {
        parent::__construct(
            "Searching config files...",
            TypeVO::Step,
            DebugMessagesVO::create([$path])
        );
    }
}