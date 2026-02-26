<?php

declare(strict_types=1);

namespace App\Core\Domain\Subscription\Collection;

use App\Core\Domain\Shared\Collection\AbstractCollection;
use App\Core\Domain\Subscription\Entity\Subscription;

/**
 * @extends AbstractCollection<Subscription>
 */
final class SubscriptionCollection extends AbstractCollection
{
    public function getType(): string
    {
        return Subscription::class;
    }

    public function __toString(): string
    {
        $subscriptionsString = '';

        foreach ($this->toArray() as $subscription) {
            $subscriptionsString .= "$subscription->name: $subscription->url\n";
        }

        return $subscriptionsString;
    }
}