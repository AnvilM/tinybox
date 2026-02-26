<?php

declare(strict_types=1);

namespace App\Core\Shared\ReporterEvent\Events\Shared\Config;

use App\Core\Shared\ReporterEvent\ReporterEvent;
use App\Core\Shared\VO\ReporterEvent\TypeVO;

final readonly class ConfigFileReadSuccessfullyReporterEvent extends ReporterEvent
{
    public function __construct()
    {
        parent::__construct(
            "Config file read successfully",
            TypeVO::Success
        );
    }
}