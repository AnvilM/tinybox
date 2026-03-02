<?php

declare(strict_types=1);

namespace App\Core\Shared\ReporterEvent\Events\RunSingBox\Process\RunSingBox;

use App\Core\Shared\ReporterEvent\ReporterEvent;
use App\Core\Shared\VO\ReporterEvent\DebugMessagesVO;
use App\Core\Shared\VO\ReporterEvent\TypeVO;

final readonly class RunSingBoxReporterEvent extends ReporterEvent
{
    public function __construct(string $command)
    {
        parent::__construct(
            "Running sing-box",
            TypeVO::Step,
            DebugMessagesVO::create([$command])
        );
    }
}