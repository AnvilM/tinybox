<?php

declare(strict_types=1);

namespace App\Application\Repository\Subscription;

use App\Application\Exception\Repository\Shared\UnableToGetListException;
use App\Application\Repository\Subscription\Shared\Builder\RawSubscriptionVOBuilder;
use App\Application\Repository\Subscription\Shared\File\ReadSubscriptions;
use App\Application\Repository\Subscription\Shared\File\WriteSubscriptions;
use App\Application\Repository\Subscription\Shared\Validator\SubscriptionsListFormatValidator;
use App\Domain\Shared\VO\Shared\NonEmptyStringVO;
use App\Domain\Subscription\Entity\Subscription;
use App\Domain\Subscription\Exception\SubscriptionNotFoundException;
use App\Domain\Subscription\Factory\FromRawSubscription\FromRawSubscriptionFactory;

final class GetSubscriptionWithNameRepository extends Shared\SubscriptionRepository
{
    public function __construct(ReadSubscriptions $readSubscriptions, SubscriptionsListFormatValidator $subscriptionsListFormatValidator, WriteSubscriptions $writeSubscriptions, RawSubscriptionVOBuilder $rawSubscriptionVOBuilder, FromRawSubscriptionFactory $fromRawSubscriptionFactory)
    {
        parent::__construct($readSubscriptions, $subscriptionsListFormatValidator, $writeSubscriptions, $rawSubscriptionVOBuilder, $fromRawSubscriptionFactory);
    }

    /**
     * Get subscription with provided name from subscriptions list
     *
     * @return Subscription Found subscription
     *
     * @throws UnableToGetListException If unable to get subscriptions list
     * @throws SubscriptionNotFoundException If subscription with provided name doesn't exist
     */
    public function get(NonEmptyStringVO $subscriptionName): Subscription
    {
        return $this->getSubscriptionsList()->getSubscriptionByName($subscriptionName);
    }
}