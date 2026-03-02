<?php

declare(strict_types=1);

namespace App\Application\Shared\DTO\Subscription;

final readonly class SubscriptionDTO
{
    public function __construct(
        public string $subscriptionName,
        public string $subscriptionUrl,
    )
    {
    }
}