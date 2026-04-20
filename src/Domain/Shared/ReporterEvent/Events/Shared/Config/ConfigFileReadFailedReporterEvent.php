<?php

declare(strict_types=1);

namespace App\Domain\Shared\ReporterEvent\Events\Shared\Config;

use App\Domain\Shared\ReporterEvent\ReporterEvent;
use App\Domain\Shared\VO\ReporterEvent\ReporterEventTypeVO;

final readonly class ConfigFileReadFailedReporterEvent extends ReporterEvent
{
    public function __construct()
    {
        parent::__construct(
            "Group file read failed, using default config",
            ReporterEventTypeVO::Warning
        );
    }
}