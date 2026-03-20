<?php

declare(strict_types=1);

namespace App\Application\Services\Subscription\ApplySubscription\Command;

final readonly class ApplySubscriptionCommand
{
    public function __construct(
        public string $subscriptionName,
        public bool   $asSystemdService
    )
    {
    }
}