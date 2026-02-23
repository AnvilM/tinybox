<?php

declare(strict_types=1);

namespace App\Core\Shared\ReporterEvent\Events\UpdateSubscriptionsLifecycle\Handler\GetSchemes;

use App\Core\Shared\ReporterEvent\ReporterEvent;
use App\Core\Shared\VO\ReporterEvent\BreadcrumbsVO;
use App\Core\Shared\VO\ReporterEvent\TypeVO;

final readonly class NoValidSchemesInSubscriptionFoundReporterEvent extends ReporterEvent
{
    public function __construct(string $subscriptionName)
    {
        parent::__construct(
            "No valid schemes found",
            TypeVO::Skipped,
            null,
            BreadcrumbsVO::create([
                $subscriptionName,
            ])
        );
    }
}