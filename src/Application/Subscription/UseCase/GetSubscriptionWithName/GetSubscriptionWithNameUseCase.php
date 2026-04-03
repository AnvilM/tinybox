<?php

declare(strict_types=1);

namespace App\Application\Subscription\UseCase\GetSubscriptionWithName;

use App\Application\Exception\Repository\Shared\UnableToGetListException;
use App\Application\Repository\Subscription\GetSubscriptionListRepository;
use App\Domain\Shared\Exception\CriticalException;
use App\Domain\Subscription\Entity\Subscription;
use App\Domain\Subscription\Exception\InvalidSubscriptionNameException;
use App\Domain\Subscription\Exception\SubscriptionNotFoundException;
use App\Domain\Subscription\VO\SubscriptionNameVO;

final readonly class GetSubscriptionWithNameUseCase
{
    public function __construct(
        private GetSubscriptionListRepository $getSubscriptionListRepository,

    )
    {
    }

    /**
     * @throws CriticalException
     */
    public function handle(string $subscriptionName): Subscription
    {
        /**
         * Try to create subscription name
         */
        try {
            $subscriptionName = new SubscriptionNameVO($subscriptionName);
        } catch (InvalidSubscriptionNameException) {
            throw new CriticalException("Invalid subscription name provided", $subscriptionName);
        }


        /**
         * Try to get subscription with provided name
         */
        try {
            $subscription = $this->getSubscriptionListRepository->getSubscriptionsList()->getSubscriptionByName($subscriptionName);
        } catch (UnableToGetListException|SubscriptionNotFoundException $e) {
            throw new CriticalException($e->getMessage(), $e->getDebugMessage());
        }

        return $subscription;
    }
}