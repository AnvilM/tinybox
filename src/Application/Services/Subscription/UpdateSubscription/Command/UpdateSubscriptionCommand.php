<?php

declare(strict_types=1);

namespace App\Application\Services\Subscription\UpdateSubscription\Command;

final readonly class UpdateSubscriptionCommand
{
    public function __construct(
        public string $subscriptionName,
    )
    {
    }
}