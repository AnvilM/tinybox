<?php

declare(strict_types=1);

namespace App\Core\Shared\ReporterEvent\Events\RunSingBox\File\CopyConfigToDefaultSingBoxConfig;

use App\Core\Shared\ReporterEvent\ReporterEvent;
use App\Core\Shared\VO\ReporterEvent\DebugMessagesVO;
use App\Core\Shared\VO\ReporterEvent\TypeVO;

final readonly class CopyConfigReporterEvent extends ReporterEvent
{
    public function __construct(string $fromPath, string $toPath)
    {
        parent::__construct(
            "Copying configuration file to default singbox configuration file",
            TypeVO::Step,
            DebugMessagesVO::create(["from: $fromPath", "to: $toPath"])
        );
    }
}