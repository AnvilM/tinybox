<?php

declare(strict_types=1);

namespace App\Core\Shared\ReporterEvent\Events\UpdateSubscriptionsLifecycle\Fetch\FetchSubscriptions;

use App\Core\Shared\ReporterEvent\ReporterEvent;
use App\Core\Shared\VO\ReporterEvent\BreadcrumbsVO;
use App\Core\Shared\VO\ReporterEvent\DebugMessagesVO;
use App\Core\Shared\VO\ReporterEvent\TypeVO;
use App\Core\Subscription\Entity\Subscription;

final readonly class SubscriptionSuccessfullyFetchedReporterEvent extends ReporterEvent
{
    public function __construct(Subscription $subscription)
    {
        parent::__construct(
            "Subscription successfully fetched",
            TypeVO::Success,
            DebugMessagesVO::create([
                $subscription->url
            ]),
            BreadcrumbsVO::create([
                $subscription->name
            ])
        );
    }
}