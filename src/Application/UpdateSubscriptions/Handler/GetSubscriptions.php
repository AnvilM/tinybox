<?php

declare(strict_types=1);

namespace App\Application\UpdateSubscriptions\Handler;

use App\Application\UpdateSubscriptions\Mapper\SubscriptionsMapper;
use App\Application\UpdateSubscriptions\Validator\SubscriptionsValidator;
use App\Core\Domain\Subscription\Collection\SubscriptionCollection;
use App\Core\Shared\Exception\CriticalException;
use App\Core\Shared\Exception\File\UnableToDecodeJSONException;
use App\Core\Shared\Exception\File\UnableToReadFileException;
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
        try {
            $rawSubscriptionArray = $this->jsonReader->read(
                $this->subscriptionConfigPort::subscriptionListPath(),
                "subscriptions"
            );
        } catch (UnableToDecodeJSONException|UnableToReadFileException $e) {
            throw new CriticalException(
                ($e instanceof UnableToDecodeJSONException)
                    ? "Unable to parse JSON at subscriptions list"
                    : "Unable to read file at subscriptions list",
                $this->subscriptionConfigPort::subscriptionListPath()
            );
        }

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