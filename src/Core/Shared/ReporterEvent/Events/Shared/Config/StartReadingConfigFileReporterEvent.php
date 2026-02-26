<?php

declare(strict_types=1);

namespace App\Core\Shared\ReporterEvent\Events\Shared\Config;

use App\Core\Shared\ReporterEvent\ReporterEvent;
use App\Core\Shared\VO\ReporterEvent\DebugMessagesVO;
use App\Core\Shared\VO\ReporterEvent\TypeVO;

final readonly class StartReadingConfigFileReporterEvent extends ReporterEvent
{
    public function __construct(string $configFilePath)
    {
        parent::__construct(
            "Reading configuration file...",
            TypeVO::Step,
            DebugMessagesVO::create([$configFilePath])
        );
    }
}