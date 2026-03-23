<?php

declare(strict_types=1);

namespace App\Application\Services\Subscription\UpdateSubscription\Handler;

use App\Application\Services\Subscription\UpdateSubscription\Command\UpdateSubscriptionCommand;
use App\Application\Shared\Subscription\Exception\UseCase\FetchSchemes\NoValidSchemesFoundException;
use App\Application\Shared\Subscription\Shared\File\WriteSubscriptions;
use App\Application\Shared\Subscription\UseCase\FetchSchemes\FetchSchemesUseCase;
use App\Application\Shared\Subscription\UseCase\ReadSubscriptionsList\ReadSubscriptionsListUseCase;
use App\Domain\Shared\Exception\CriticalException;
use App\Domain\Subscription\Exception\InvalidSubscriptionNameException;
use App\Domain\Subscription\Exception\SubscriptionNotFoundException;
use App\Domain\Subscription\VO\SubscriptionNameVO;

final readonly class UpdateSubscriptionHandler
{

    public function __construct(
        private ReadSubscriptionsListUseCase $readSubscriptionsListUseCase,
        private FetchSchemesUseCase          $fetchSchemesUseCase,
        private WriteSubscriptions           $writeSubscriptions
    )
    {
    }

    /**
     * @throws CriticalException
     */
    public function handle(UpdateSubscriptionCommand $command): void
    {
        /**
         * Try to create subscription name
         */
        try {
            $subscriptionName = new SubscriptionNameVO($command->subscriptionName);
        } catch (InvalidSubscriptionNameException) {
            throw new CriticalException("Invalid subscription name provided");
        }


        /**
         * Read subscriptions list
         */
        $subscriptions = $this->readSubscriptionsListUseCase->handle();


        /**
         * Try to find subscription with provided name
         */
        try {
            $subscription = $subscriptions->getSubscriptionByName($subscriptionName);
        } catch (SubscriptionNotFoundException) {
            throw new CriticalException("Subscription with name {$subscriptionName->getName()} does not exist");
        }


        /**
         * Try to fetch subscription schemes
         */
        try {
            $schemes = $this->fetchSchemesUseCase->handle($subscription->getUrlVO());
        } catch (NoValidSchemesFoundException) {
            throw new CriticalException ("No valid schemes found", $subscription->getUrl());
        }


        /**
         * Set subscription schemes
         */
        $subscription->setSchemes($schemes);


        /**
         * Write subscriptions to file
         */
        $this->writeSubscriptions->write($subscriptions);
    }

}