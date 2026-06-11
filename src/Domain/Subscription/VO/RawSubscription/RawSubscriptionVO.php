<?php

declare(strict_types=1);

namespace App\Domain\Subscription\VO\RawSubscription;

abstract readonly class RawSubscriptionVO
{
    public function __construct(
        public string $name,
        public string $url,
        public string $type
    )
    {
    }
}