<?php

declare(strict_types=1);

namespace App\Application\Repository\Subscription;

use App\Application\Exception\Repository\Shared\UnableToGetListException;
use App\Application\Repository\Subscription\Shared\Builder\RawSubscriptionVOBuilder;
use App\Application\Repository\Subscription\Shared\File\ReadSubscriptions;
use App\Application\Repository\Subscription\Shared\File\WriteSubscriptions;
use App\Application\Repository\Subscription\Shared\SubscriptionRepository;
use App\Application\Repository\Subscription\Shared\Validator\SubscriptionsListFormatValidator;
use App\Domain\Shared\VO\Shared\NonEmptyStringVO;
use App\Domain\Subscription\Collection\SubscriptionsMap;
use App\Domain\Subscription\Factory\FromRawSubscription\FromRawSubscriptionFactory;

final class RemoveSubscriptionRepository extends SubscriptionRepository
{
    public function __construct(ReadSubscriptions $readSubscriptions, SubscriptionsListFormatValidator $subscriptionsListFormatValidator, WriteSubscriptions $writeSubscriptions, RawSubscriptionVOBuilder $rawSubscriptionVOBuilder, FromRawSubscriptionFactory $fromRawSubscriptionFactory)
    {
        parent::__construct($readSubscriptions, $subscriptionsListFormatValidator, $writeSubscriptions, $rawSubscriptionVOBuilder, $fromRawSubscriptionFactory);
    }

    /**
     * Remove subscription with provided name
     *
     * NOTE: Method doesn't write subscriptions list to file. Use method save
     *
     * @param NonEmptyStringVO $subscriptionName Subscription name to remove
     *
     * @throws UnableToGetListException If unable to get subscriptions list
     */
    public function remove(NonEmptyStringVO $subscriptionName): self
    {
        $this->getSubscriptionsList()->removeByName($subscriptionName);

        return $this;
    }


    /**
     * @inheritdoc
     */
    public function save(): SubscriptionsMap
    {
        return parent::save();
    }
}