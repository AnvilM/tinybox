<?php

declare(strict_types=1);

namespace App\Application\ReadSubscriptionList\Command;

use App\Application\Shared\DTO\Subscription\SubscriptionCollectionDTO;

final readonly class ReadSubscriptionsListCommandResult
{
    public function __construct(
        public SubscriptionCollectionDTO $rawSubscriptionCollectionDTO,
    )
    {
    }

}