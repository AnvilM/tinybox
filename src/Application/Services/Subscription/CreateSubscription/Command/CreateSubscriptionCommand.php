<?php

declare(strict_types=1);

namespace App\Application\Services\Subscription\CreateSubscription\Command;

final readonly class CreateSubscriptionCommand
{
    public function __construct(
        public string $name,
        public string $url,
    )
    {
    }
}