<?php

declare(strict_types=1);

namespace App\Core\Shared\ReporterEvent\Events\FetchSubscriptions\Fetch\FetchSubscriptions;

use App\Core\Domain\Subscription\Collection\SubscriptionCollection;
use App\Core\Shared\ReporterEvent\ReporterEvent;
use App\Core\Shared\VO\ReporterEvent\DebugMessagesVO;
use App\Core\Shared\VO\ReporterEvent\TypeVO;

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