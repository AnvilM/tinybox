<?php

declare(strict_types=1);

namespace App\Application\Repository\Subscription;

use App\Application\Exception\Repository\Shared\UnableToGetListException;
use App\Application\Repository\Outbound\GetOutboundsListRepository;
use App\Application\Repository\Subscription\Shared\File\ReadSubscriptions;
use App\Application\Repository\Subscription\Shared\File\WriteSubscriptions;
use App\Application\Repository\Subscription\Shared\SubscriptionRepository;
use App\Application\Repository\Subscription\Shared\Validator\SubscriptionsListFormatValidator;
use App\Domain\Subscription\Collection\SubscriptionsMap;
use App\Domain\Subscription\Entity\Subscription;
use App\Domain\Subscription\Exception\SubscriptionAlreadyExistsException;

final class AddSubscriptionRepository extends SubscriptionRepository
{
    public function __construct(ReadSubscriptions $readSubscriptions, SubscriptionsListFormatValidator $subscriptionsListFormatValidator, GetOutboundsListRepository $getOutboundsList, WriteSubscriptions $writeSubscriptions)
    {
        parent::__construct($readSubscriptions, $subscriptionsListFormatValidator, $getOutboundsList, $writeSubscriptions);
    }

    /**
     * Add subscription to subscriptions list
     *
     * NOTE: Method doesn't write subscriptions list to file. Use method save
     *
     * @param Subscription $subscription Subscription to add to subscriptions list
     *
     * @return self Current AddSubscription object
     *
     * @throws UnableToGetListException If unable to get subscriptions list
     * @throws SubscriptionAlreadyExistsException If provided subscription already exist in subscriptions list
     */
    public function add(Subscription $subscription): self
    {
        $this->getSubscriptionsList()->add($subscription);

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