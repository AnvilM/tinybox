<?php

declare(strict_types=1);

namespace App\Application\UpdateSubscriptions\Handler;

use App\Application\UpdateSubscriptions\Mapper\SubscriptionsMapper;
use App\Application\UpdateSubscriptions\Validator\SubscriptionsValidator;
use App\Core\Domain\Subscription\Collection\SubscriptionCollection;
use App\Core\Shared\Exception\CriticalException;
use App\Core\Shared\Exception\File\UnableToDecodeJSONException;
use App\Core\Shared\Exception\File\UnableToReadFileException;
use App\Core\Shared\Ports\Config\ConfigFactoryPort;
use App\Core\Shared\Ports\File\JsonReaderPort;

final readonly class GetSubscriptions
{
    public function __construct(
        private SubscriptionsMapper    $subscriptionListMapper,
        private SubscriptionsValidator $subscriptionListValidation,
        private JsonReaderPort         $jsonReader,
        private ConfigFactoryPort      $configFactoryPort,
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
                $this->configFactoryPort->get()->subscriptionListPath,
            );
        } catch (UnableToDecodeJSONException|UnableToReadFileException $e) {
            throw new CriticalException(
                ($e instanceof UnableToDecodeJSONException)
                    ? "Unable to parse JSON at subscriptions list"
                    : "Unable to read file at subscriptions list",
                $this->configFactoryPort->get()->subscriptionListPath
            );
        }

        $this->subscriptionListValidation->validate($rawSubscriptionArray);

        $subscriptionCollection = $this->subscriptionListMapper->map($rawSubscriptionArray);

        if ($subscriptionName !== null) {
            foreach ($subscriptionCollection as $subscription) {
                if ($subscription->name === $subscriptionName) return SubscriptionCollection::create([$subscription]);
            }

            throw new CriticalException("No subscription $subscriptionName</bold> found</red>");
        }


        return $subscriptionCollection;
    }
}