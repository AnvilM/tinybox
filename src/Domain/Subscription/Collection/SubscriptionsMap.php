<?php

declare(strict_types=1);

namespace App\Domain\Subscription\Collection;

use App\Domain\Shared\Exception\Json\UnableToEncodeJsonException;
use App\Domain\Shared\VO\Shared\NonEmptyStringVO;
use App\Domain\Subscription\Entity\Subscription;
use App\Domain\Subscription\Exception\SubscriptionAlreadyExistsException;
use App\Domain\Subscription\Exception\SubscriptionNotFoundException;
use JsonException;
use Psl\Collection\MutableMap;

final readonly class SubscriptionsMap
{
    /**
     * Subscriptions map <subscriptionName: string, subscription: Subscription>
     *
     * @var MutableMap<string, Subscription>
     */
    private MutableMap $map;

    public function __construct()
    {
        $this->map = new MutableMap([]);
    }

    /**
     * Add subscription to map
     *
     * @param Subscription $subscription Subscription
     *
     * @throws SubscriptionAlreadyExistsException If subscription with provided name or url already exists in map
     */
    public function add(Subscription $subscription): void
    {
        /**
         * Check if subscription with provided name or url already exists in map
         */
        if ($this->containsSubscription(
            $subscription->getNameVO()
        )) throw new SubscriptionAlreadyExistsException();


        /**
         * Add subscription to map
         */
        $this->map->add($subscription->getNameString(), $subscription);
    }

    /**
     * Check map contains subscription with provided name
     *
     * @param NonEmptyStringVO $subscriptionName Subscription name
     *
     * @return bool Returns true if map contains subscription with provided name
     */
    public function containsSubscription(NonEmptyStringVO $subscriptionName): bool
    {
        foreach ($this->map as $subscription) {
            if ($subscription->getNameVO()->equals($subscriptionName)) return true;
        }

        return false;
    }


    /**
     * Convert subscriptions map to JSON
     *
     * [{'name': 'sub1', 'url': 'https://...', 'schemes': ['scheme1', 'scheme2', ...]},
     * {'name': 'sub2', 'url': 'http://...', 'schemes': [...]},
     * ...]
     *
     * @return string JSON
     *
     * @throws UnableToEncodeJsonException If unable to encode json
     */
    public function toJson(): string
    {
        /**
         * Assert map is not empty
         */
        if ($this->map->isEmpty()) return '[]';


        $array = [];


        /**
         * Mapping map to array
         */
        foreach ($this->map as $subscription) {
            $array[] = $subscription->toArray();
        }


        /**
         * Try to encode JSON
         */
        try {
            return json_encode(
                $array,
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR
            );
        } catch (JsonException) {
            throw new UnableToEncodeJsonException();
        }
    }


    /**
     * Get subscriptions as name => url mutable map
     *
     * @return MutableMap<string, string> Subscriptions as name => url mutable map
     */
    public function toNameUrlMap(): MutableMap
    {
        return $this->map->map(fn(Subscription $subscription) => $subscription->getUrl());
    }


    /**
     * Get subscription with specific name
     *
     * @param NonEmptyStringVO $subscriptionName Subscription name
     *
     * @return Subscription Subscription with provided name
     *
     * @throws SubscriptionNotFoundException If subscription with provided name not found
     */
    public function getSubscriptionByName(NonEmptyStringVO $subscriptionName): Subscription
    {
        $subscription = $this->map->get($subscriptionName->getValue());

        if ($subscription === null) throw new SubscriptionNotFoundException();

        return $subscription;
    }


    /**
     * Remove subscription by name
     *
     * @param NonEmptyStringVO $subscriptionName Subscription name to remove
     */
    public function removeByName(NonEmptyStringVO $subscriptionName): void
    {
        $this->map->remove($subscriptionName->getValue());
    }
}