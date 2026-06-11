<?php

declare(strict_types=1);

namespace App\Application\Repository\Subscription;

use App\Application\Repository\Subscription\Shared\Builder\RawSubscriptionVOBuilder;
use App\Application\Repository\Subscription\Shared\File\ReadSubscriptions;
use App\Application\Repository\Subscription\Shared\File\WriteSubscriptions;
use App\Application\Repository\Subscription\Shared\SubscriptionRepository;
use App\Application\Repository\Subscription\Shared\Validator\SubscriptionsListFormatValidator;
use App\Domain\Subscription\Collection\SubscriptionsMap;
use App\Domain\Subscription\Factory\FromRawSubscription\FromRawSubscriptionFactory;

final class GetSubscriptionListRepository extends SubscriptionRepository
{
    public function __construct(ReadSubscriptions $readSubscriptions, SubscriptionsListFormatValidator $subscriptionsListFormatValidator, WriteSubscriptions $writeSubscriptions, RawSubscriptionVOBuilder $rawSubscriptionVOBuilder, FromRawSubscriptionFactory $fromRawSubscriptionFactory)
    {
        parent::__construct($readSubscriptions, $subscriptionsListFormatValidator, $writeSubscriptions, $rawSubscriptionVOBuilder, $fromRawSubscriptionFactory);
    }


    /**
     * @inheritdoc
     */
    public function getSubscriptionsList(): SubscriptionsMap
    {
        return parent::getSubscriptionsList();
    }
}