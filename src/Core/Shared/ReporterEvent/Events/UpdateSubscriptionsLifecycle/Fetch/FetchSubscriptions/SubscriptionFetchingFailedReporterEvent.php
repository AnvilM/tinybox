<?php

declare(strict_types=1);

namespace App\Core\Shared\ReporterEvent\Events\UpdateSubscriptionsLifecycle\Fetch\FetchSubscriptions;

use App\Core\Domain\Subscription\Entity\Subscription;
use App\Core\Shared\ReporterEvent\ReporterEvent;
use App\Core\Shared\VO\ReporterEvent\BreadcrumbsVO;
use App\Core\Shared\VO\ReporterEvent\DebugMessagesVO;
use App\Core\Shared\VO\ReporterEvent\TypeVO;
use Exception;

final readonly class SubscriptionFetchingFailedReporterEvent extends ReporterEvent
{
    public function __construct(Exception $exception, Subscription $subscription)
    {
        parent::__construct(
            "Unable to load subscription",
            TypeVO::Skipped,
            DebugMessagesVO::create([
                $subscription->url,
                $exception->getMessage(),
            ]),
            BreadcrumbsVO::create([
                $subscription->name
            ])
        );
    }
}