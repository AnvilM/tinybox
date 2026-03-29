<?php

declare(strict_types=1);

namespace App\Application\Services\Subscription\TestSubscription\Command;

final readonly class TestSubscriptionCommand
{
    public function __construct(
        public string  $subscriptionName,
        public ?string $testMethod
    )
    {
    }
}