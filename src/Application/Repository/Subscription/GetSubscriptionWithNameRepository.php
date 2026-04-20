<?php

declare(strict_types=1);

namespace App\Application\Repository\Subscription;

use App\Application\Exception\Repository\Shared\UnableToGetListException;
use App\Application\Repository\Outbound\GetOutboundsListRepository;
use App\Application\Repository\Subscription\Shared\File\ReadSubscriptions;
use App\Application\Repository\Subscription\Shared\File\WriteSubscriptions;
use App\Application\Repository\Subscription\Shared\Validator\SubscriptionsListFormatValidator;
use App\Domain\Shared\VO\Shared\NonEmptyStringVO;
use App\Domain\Subscription\Entity\Subscription;
use App\Domain\Subscription\Exception\SubscriptionNotFoundException;
use App\Domain\Subscription\VO\SubscriptionNameVO;

final class GetSubscriptionWithNameRepository extends Shared\SubscriptionRepository
{
    public function __construct(ReadSubscriptions $readSubscriptions, SubscriptionsListFormatValidator $subscriptionsListFormatValidator, GetOutboundsListRepository $getOutboundsList, WriteSubscriptions $writeSubscriptions)
    {
        parent::__construct($readSubscriptions, $subscriptionsListFormatValidator, $getOutboundsList, $writeSubscriptions);
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
        return $this->getSubscriptionsList()->getSubscriptionByName(SubscriptionNameVO::fromNonEmptyString($subscriptionName));
    }
}