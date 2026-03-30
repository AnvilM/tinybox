<?php

declare(strict_types=1);

namespace App\Application\Repository\Subscription;

use App\Application\Exception\Repository\Shared\UnableToGetListException;
use App\Application\Repository\Scheme\GetSchemesListRepository;
use App\Application\Repository\Subscription\Shared\File\ReadSubscriptions;
use App\Application\Repository\Subscription\Shared\File\WriteSubscriptions;
use App\Application\Repository\Subscription\Shared\SubscriptionRepository;
use App\Application\Repository\Subscription\Shared\Validator\SubscriptionsListFormatValidator;
use App\Domain\Scheme\Collection\UniqueSchemesMap;
use App\Domain\Shared\VO\Shared\NonEmptyStringVO;
use App\Domain\Subscription\Collection\SubscriptionsMap;
use App\Domain\Subscription\Entity\Subscription;
use App\Domain\Subscription\Exception\SubscriptionNotFoundException;
use App\Domain\Subscription\VO\SubscriptionNameVO;

final class UpdateSubscriptionSchemesRepository extends SubscriptionRepository
{
    public function __construct(ReadSubscriptions $readSubscriptions, SubscriptionsListFormatValidator $subscriptionsListFormatValidator, GetSchemesListRepository $getSchemesList, WriteSubscriptions $writeSubscriptions)
    {
        parent::__construct($readSubscriptions, $subscriptionsListFormatValidator, $getSchemesList, $writeSubscriptions);
    }

    /**
     * Get subscription with provided name from subscriptions list
     *
     * NOTE: Method doesn't write subscriptions list to file. Use method save
     *
     * @param NonEmptyStringVO $subscriptionName Subscription name to add schemes
     * @param UniqueSchemesMap $schemes Schemes to add to subscription
     *
     * @return Subscription Found subscription
     *
     * @throws UnableToGetListException If unable to get subscriptions list
     * @throws SubscriptionNotFoundException If subscription with provided name doesn't exist
     */
    public function update(NonEmptyStringVO $subscriptionName, UniqueSchemesMap $schemes): Subscription
    {
        $subscription = $this->getSubscriptionsList()->getSubscriptionByName(SubscriptionNameVO::fromNonEmptyString($subscriptionName));
        $subscription->setSchemes($schemes);
        return $subscription;
    }


    /**
     * @inheritdoc
     */
    public function save(): SubscriptionsMap
    {
        return parent::save();
    }
}