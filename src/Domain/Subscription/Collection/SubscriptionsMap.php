<?php

declare(strict_types=1);

namespace App\Domain\Subscription\Collection;

use App\Domain\Shared\Exception\Json\UnableToEncodeJsonException;
use App\Domain\Subscription\Entity\Subscription;
use App\Domain\Subscription\Exception\SubscriptionAlreadyExistsException;
use App\Domain\Subscription\VO\SubscriptionNameVO;
use App\Domain\Subscription\VO\SubscriptionURLVO;
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
        if ($this->containsSubscriptionUrlOrName(
            $subscription->getUrlVO(),
            $subscription->getNameVO()
        )) throw new SubscriptionAlreadyExistsException();


        /**
         * Add subscription to map
         */
        $this->map->add($subscription->getName(), $subscription);
    }

    /**
     * Check map contains subscription with provided url or name
     *
     * @param SubscriptionURLVO $subscriptionUrl Subscription url
     * @param SubscriptionNameVO $subscriptionName Subscription name
     *
     * @return bool Returns true if map contains subscription with provided url or name
     */
    public function containsSubscriptionUrlOrName(SubscriptionURLVO $subscriptionUrl, SubscriptionNameVO $subscriptionName): bool
    {
        foreach ($this->map as $subscription) {

            if (
                $subscription->getUrl() === $subscriptionUrl->getUrl()
                || $subscription->getName() === $subscriptionName->getName()
            ) return true;
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
            $array[] = [
                'name' => $subscription->getName(),
                'url' => $subscription->getUrl(),
                'schemes' => $subscription->getSchemes()->getIds()->toArray(),
            ];
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
}