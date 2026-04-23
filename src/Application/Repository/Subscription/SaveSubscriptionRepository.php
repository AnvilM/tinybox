<?php

declare(strict_types=1);

namespace App\Application\Repository\Subscription;

use App\Application\Repository\Outbound\GetOutboundsListRepository;
use App\Application\Repository\Subscription\Shared\File\ReadSubscriptions;
use App\Application\Repository\Subscription\Shared\File\WriteSubscriptions;
use App\Application\Repository\Subscription\Shared\SubscriptionRepository;
use App\Application\Repository\Subscription\Shared\Validator\SubscriptionsListFormatValidator;
use App\Domain\Subscription\Collection\SubscriptionsMap;

final class SaveSubscriptionRepository extends SubscriptionRepository
{
    public function __construct(ReadSubscriptions $readSubscriptions, SubscriptionsListFormatValidator $subscriptionsListFormatValidator, GetOutboundsListRepository $getOutboundsList, WriteSubscriptions $writeSubscriptions)
    {
        parent::__construct($readSubscriptions, $subscriptionsListFormatValidator, $getOutboundsList, $writeSubscriptions);
    }


    /**
     * @inheritdoc
     */
    public function save(): SubscriptionsMap
    {
        return parent::save();
    }
}