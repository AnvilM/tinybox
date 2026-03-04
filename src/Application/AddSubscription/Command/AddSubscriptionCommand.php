<?php

declare(strict_types=1);

namespace App\Application\AddSubscription\Command;

final readonly class AddSubscriptionCommand
{
    public function __construct(
        public string $subscriptionName,
        public string $subscriptionUrl
    )
    {
    }
}