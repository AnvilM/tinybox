<?php

declare(strict_types=1);

namespace App\Core\Shared\ReporterEvent\Events\UpdateSubscriptionsLifecycle\Handler\CreateSingBoxConfig;

use App\Core\Shared\ReporterEvent\ReporterEvent;
use App\Core\Shared\VO\ReporterEvent\BreadcrumbsVO;
use App\Core\Shared\VO\ReporterEvent\TypeVO;

final readonly class NoValidOutboundsInSubscriptionFoundReporterEvent extends ReporterEvent
{
    public function __construct(string $subscriptionName)
    {
        parent::__construct(
            "No valid outbounds found",
            TypeVO::Skipped,
            null,
            BreadcrumbsVO::create([
                $subscriptionName,
            ])
        );
    }
}