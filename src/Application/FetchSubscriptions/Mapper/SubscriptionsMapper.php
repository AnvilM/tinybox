<?php

declare(strict_types=1);

namespace App\Application\FetchSubscriptions\Mapper;

use App\Application\Shared\DTO\Subscription\SubscriptionCollectionDTO;
use App\Core\Domain\Subscription\Collection\SubscriptionCollection;
use App\Core\Domain\Subscription\Entity\Subscription;
use App\Core\Shared\Exception\CriticalException;
use InvalidArgumentException;

final readonly class SubscriptionsMapper
{
    /**
     * Maps subscriptions collection dto to subscription collection
     *
     * @param SubscriptionCollectionDTO $subscriptionCollectionDTO Subscriptions collection dto
     *
     * @return SubscriptionCollection Subscription collection
     *
     * @throws CriticalException Throws if no one subscription dto in subscriptions collection dto found
     */
    public function map(SubscriptionCollectionDTO $subscriptionCollectionDTO): SubscriptionCollection
    {
        try {
            return SubscriptionCollection::create(
                $subscriptionCollectionDTO->map(
                    fn($subscriptionDTO) => new Subscription($subscriptionDTO->subscriptionName, $subscriptionDTO->subscriptionUrl),
                )->toArray()
            );
        } catch (InvalidArgumentException) {
            throw new CriticalException("No one subscription found");
        }


    }
}