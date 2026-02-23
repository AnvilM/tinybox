<?php

declare(strict_types=1);

namespace App\Application\UpdateSubscriptions\Mapper;

use App\Core\Shared\Exception\CriticalException;
use App\Core\Subscription\Collection\SubscriptionCollection;
use App\Core\Subscription\Entity\Subscription;
use InvalidArgumentException;

final readonly class SubscriptionsMapper
{
    /**
     * Maps raw array of subscriptions to subscription collection
     *
     * @param array<string, string> $subscriptionsArray Array of subscriptions ["subscriptionName" => "subscriptionUrl"]
     *
     * @return SubscriptionCollection Subscription collection
     *
     * @throws CriticalException
     */
    public function map(array $subscriptionsArray): SubscriptionCollection
    {
        try {
            return SubscriptionCollection::create(
                array_map(
                    fn($subscriptionName, $subscriptionUrl) => new Subscription($subscriptionName, $subscriptionUrl),
                    array_keys($subscriptionsArray),
                    $subscriptionsArray
                )
            );
        } catch (InvalidArgumentException) {
            throw new CriticalException("No one subscription found");
        }


    }
}