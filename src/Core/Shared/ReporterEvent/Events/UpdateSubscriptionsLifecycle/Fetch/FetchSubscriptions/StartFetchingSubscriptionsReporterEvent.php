<?php

declare(strict_types=1);

namespace App\Core\Shared\ReporterEvent\Events\UpdateSubscriptionsLifecycle\Fetch\FetchSubscriptions;

use App\Core\Shared\ReporterEvent\ReporterEvent;
use App\Core\Shared\VO\ReporterEvent\DebugMessagesVO;
use App\Core\Shared\VO\ReporterEvent\TypeVO;
use App\Core\Subscription\Collection\SubscriptionCollection;

final readonly class StartFetchingSubscriptionsReporterEvent extends ReporterEvent
{
    public function __construct(SubscriptionCollection $subscriptions)
    {
        parent::__construct(
            "Fetching subscriptions...",
            TypeVO::Step,
            DebugMessagesVO::create([$subscriptions->__toString()])
        );
    }
}