<?php

declare(strict_types=1);

namespace App\Application\FetchSubscriptions\Command;

use App\Application\Shared\DTO\Subscription\SubscriptionCollectionDTO;

final readonly class FetchSubscriptionsCommand
{
    public function __construct(
        public SubscriptionCollectionDTO $subscriptionsCollectionDTO,
        public ?string                   $subscriptionName,
    )
    {
    }
}