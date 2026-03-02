<?php

declare(strict_types=1);

namespace App\Application\ReadSubscriptionList\Mapper;

use App\Application\Shared\DTO\Subscription\SubscriptionCollectionDTO;
use App\Application\Shared\DTO\Subscription\SubscriptionDTO;
use App\Core\Shared\Exception\CriticalException;
use InvalidArgumentException;

final readonly class SubscriptionDTOMapper
{
    /**
     * Maps raw array of subscriptions to raw subscription collection dto
     *
     * @param array<string, string> $subscriptionsArray Array of subscriptions e.g., ["subscriptionName" => "subscriptionUrl", ...]
     *
     * @return SubscriptionCollectionDTO Raw subscription collection dto
     *
     * @throws CriticalException Throws if no subscriptions in array provided
     */
    public function map(array $subscriptionsArray): SubscriptionCollectionDTO
    {
        try {
            return SubscriptionCollectionDTO::create(
                array_map(
                    fn($subscriptionName, $subscriptionUrl) => new SubscriptionDTO($subscriptionName, $subscriptionUrl),
                    array_keys($subscriptionsArray),
                    $subscriptionsArray
                )
            );
        } catch (InvalidArgumentException) {
            throw new CriticalException("No one subscription found");
        }
    }
}