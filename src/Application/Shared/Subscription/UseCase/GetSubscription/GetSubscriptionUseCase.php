<?php

declare(strict_types=1);

namespace App\Application\Shared\Subscription\UseCase\GetSubscription;

use App\Application\Shared\Subscription\Shared\UseCase\ReadSubscriptionsList\ReadSubscriptionsListUseCase;
use App\Domain\Shared\Exception\CriticalException;
use App\Domain\Subscription\Entity\Subscription;
use App\Domain\Subscription\Exception\InvalidSubscriptionNameException;
use App\Domain\Subscription\Exception\SubscriptionNotFoundException;
use App\Domain\Subscription\VO\SubscriptionNameVO;

final readonly class GetSubscriptionUseCase
{
    public function __construct(
        private ReadSubscriptionsListUseCase $readSubscriptionsListUseCase,
    )
    {
    }

    /**
     * Get subscription with provided name
     *
     * @throws CriticalException
     */
    public function handle(string $subscriptionName): Subscription
    {
        /**
         * Read list of all saved subscriptions
         */
        $subscriptions = $this->readSubscriptionsListUseCase->handle();


        /**
         * Try to create subscription name
         */
        try {
            $subscriptionName = new SubscriptionNameVO($subscriptionName);
        } catch (InvalidSubscriptionNameException) {
            throw new CriticalException("Invalid subscription name provided");
        }


        /**
         * Try to find subscription with provided name
         */
        try {
            return $subscriptions->getSubscriptionByName($subscriptionName);
        } catch (SubscriptionNotFoundException) {
            throw new CriticalException("Subscription with name {$subscriptionName->getName()} not found");
        }

    }
}