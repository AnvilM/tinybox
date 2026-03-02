<?php

declare(strict_types=1);

namespace App\Application\Shared\DTO\Subscription;

use App\Core\Domain\Shared\Collection\AbstractCollection;

final class SubscriptionCollectionDTO extends AbstractCollection
{
    public function getType(): string
    {
        return SubscriptionDTO::class;
    }

}