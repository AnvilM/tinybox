<?php

declare(strict_types=1);

namespace App\Core\Shared\ReporterEvent\Events\UpdateSubscriptionsLifecycle\File\SaveSingBoxConfig;

use App\Core\Shared\ReporterEvent\ReporterEvent;
use App\Core\Shared\VO\ReporterEvent\BreadcrumbsVO;
use App\Core\Shared\VO\ReporterEvent\DebugMessagesVO;
use App\Core\Shared\VO\ReporterEvent\TypeVO;

final readonly class StartSavingSingBoxConfigFileReporterEvent extends ReporterEvent
{
    public function __construct(string $subscriptionName, string $path)
    {
        parent::__construct(
            "Saving sing-box config...",
            TypeVO::Step,
            DebugMessagesVO::create([
                "Saving to $path",
            ]),
            BreadcrumbsVO::create([$subscriptionName])
        );
    }
}