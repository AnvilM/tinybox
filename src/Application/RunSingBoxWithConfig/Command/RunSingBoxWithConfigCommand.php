<?php

declare(strict_types=1);

namespace App\Application\ApplySubscriptionConfig\Command;

final readonly class ApplySubscriptionConfigCommand
{
    public function __construct(
        public ?string $subscriptionName,
    )
    {
    }
}