<?php

declare(strict_types=1);

namespace App\Core\Shared\ReporterEvent\Events\RunSingBox\Process\RestartSingBoxSystemd;

use App\Core\Shared\ReporterEvent\ReporterEvent;
use App\Core\Shared\VO\ReporterEvent\BreadcrumbsVO;
use App\Core\Shared\VO\ReporterEvent\DebugMessagesVO;
use App\Core\Shared\VO\ReporterEvent\TypeVO;

final readonly class SingBoxSystemdSuccessfullyRestarted extends ReporterEvent
{
    public function __construct(string $command, string $configName)
    {
        parent::__construct(
            "Sing-box systemd service successfully restarted",
            TypeVO::Success,
            DebugMessagesVO::create([$command]),
            BreadcrumbsVO::create([$configName])
        );
    }
}