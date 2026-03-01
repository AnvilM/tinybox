<?php

declare(strict_types=1);

namespace App\Application\FetchSubscriptions\Command;

final readonly class UpdateSubscriptionsCommand
{
    public function __construct(
        public ?string $subscriptionName
    )
    {
    }
}