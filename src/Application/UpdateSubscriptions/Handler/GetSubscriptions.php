<?php

declare(strict_types=1);

namespace App\Application\UpdateSubscriptions\Handler;

use App\Application\UpdateSubscriptions\Mapper\SubscriptionsMapper;
use App\Application\UpdateSubscriptions\Validator\SubscriptionsValidator;
use App\Core\Domain\Subscription\Collection\SubscriptionCollection;
use App\Core\Shared\Exception\CriticalException;
use App\Core\Shared\Ports\Config\Subscription\SubscriptionConfigPort;
use App\Core\Shared\Ports\File\JsonReaderPort;

final readonly class GetSubscriptions
{
    public function __construct(
        private SubscriptionsMapper    $subscriptionListMapper,
        private SubscriptionsValidator $subscriptionListValidation,
        private JsonReaderPort         $jsonReader,
        private SubscriptionConfigPort $subscriptionConfigPort,
    )
    {
    }

    /**
     * @throws CriticalException
     */
    public function get(?string $subscriptionName): SubscriptionCollection
    {
        $rawSubscriptionArray = $this->jsonReader->read(
            $this->subscriptionConfigPort::subscriptionListPath(),
            "subscriptions",
            "Subscriptions list successfully loaded"
        );

        $this->subscriptionListValidation->validate($rawSubscriptionArray);

        $subscriptionCollection = $this->subscriptionListMapper->map($rawSubscriptionArray);

        if ($subscriptionName !== null) {
            foreach ($subscriptionCollection as $subscription) {
                if ($subscription->name === $subscriptionName) return SubscriptionCollection::create([$subscription]);
            }

            throw new CriticalException("<red>No subscription <bold>$subscriptionName</bold> found</red>");
        }


        return $subscriptionCollection;
    }
}