<?php

declare(strict_types=1);

namespace App\Application\Repository\Subscription;

use App\Application\Repository\Scheme\GetSchemesListRepository;
use App\Application\Repository\Subscription\Shared\File\ReadSubscriptions;
use App\Application\Repository\Subscription\Shared\File\WriteSubscriptions;
use App\Application\Repository\Subscription\Shared\SubscriptionRepository;
use App\Application\Repository\Subscription\Shared\Validator\SubscriptionsListFormatValidator;
use App\Domain\Subscription\Collection\SubscriptionsMap;

final class GetSubscriptionListRepository extends SubscriptionRepository
{
    public function __construct(ReadSubscriptions $readSubscriptions, SubscriptionsListFormatValidator $subscriptionsListFormatValidator, GetSchemesListRepository $getSchemesList, WriteSubscriptions $writeSubscriptions)
    {
        parent::__construct($readSubscriptions, $subscriptionsListFormatValidator, $getSchemesList, $writeSubscriptions);
    }


    /**
     * @inheritdoc
     */
    public function getSubscriptionsList(): SubscriptionsMap
    {
        return parent::getSubscriptionsList();
    }
}