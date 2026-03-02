<?php

declare(strict_types=1);

namespace App\Core\Shared\ReporterEvent\Events\RunSingBox\File\CopyConfigToDefaultSingBoxConfig;

use App\Core\Shared\ReporterEvent\ReporterEvent;
use App\Core\Shared\VO\ReporterEvent\BreadcrumbsVO;
use App\Core\Shared\VO\ReporterEvent\DebugMessagesVO;
use App\Core\Shared\VO\ReporterEvent\TypeVO;

final readonly class ConfigSuccessfullyCopiedReporterEvent extends ReporterEvent
{
    public function __construct(string $sourcePath, string $targetPath, string $configName)
    {
        parent::__construct(
            "Config successfully copied",
            TypeVO::Success,
            DebugMessagesVO::create(["from: $sourcePath", "to: $targetPath"]),
            BreadcrumbsVO::create([$configName])
        );
    }
}