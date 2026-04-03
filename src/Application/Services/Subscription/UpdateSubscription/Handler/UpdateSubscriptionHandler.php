<?php

declare(strict_types=1);

namespace App\Application\Services\Subscription\UpdateSubscription\Handler;

use App\Application\Exception\Repository\Shared\UnableToGetListException;
use App\Application\Exception\Repository\Shared\UnableToSaveListException;
use App\Application\Exception\Services\Shared\FetchSchemes\NoValidSchemesFoundException;
use App\Application\Repository\Subscription\GetSubscriptionListRepository;
use App\Application\Repository\Subscription\SaveSubscriptionRepository;
use App\Application\Repository\Subscription\UpdateSubscriptionSchemesRepository;
use App\Application\Services\Subscription\Shared\FetchSchemes\FetchSchemesUseCase;
use App\Application\Services\Subscription\UpdateSubscription\Command\UpdateSubscriptionCommand;
use App\Domain\Shared\Exception\CriticalException;
use App\Domain\Subscription\Exception\InvalidSubscriptionNameException;
use App\Domain\Subscription\Exception\SubscriptionNotFoundException;
use App\Domain\Subscription\VO\SubscriptionNameVO;

final readonly class UpdateSubscriptionHandler
{

    public function __construct(
        private GetSubscriptionListRepository       $getSubscriptionListRepository,
        private FetchSchemesUseCase                 $fetchSchemesUseCase,
        private UpdateSubscriptionSchemesRepository $updateSubscriptionSchemesRepository,
        private SaveSubscriptionRepository          $saveSubscriptionRepository,
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
         * Try to read subscriptions list
         */
        try {
            $subscriptions = $this->getSubscriptionListRepository->getSubscriptionsList();
        } catch (UnableToGetListException $e) {
            throw new CriticalException("Unable to get subscriptions list", $e->getDebugMessage());
        }


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
         * Try to update subscription
         */
        try {
            $this->updateSubscriptionSchemesRepository->update($subscriptionName, $schemes);
        } catch (UnableToGetListException|SubscriptionNotFoundException $e) {
            throw new CriticalException($e instanceof UnableToGetListException
                ? "Unable to get subscriptions list"
                : "Subscription with name {$subscriptionName->getName()} does not exist", $e->getDebugMessage()
            );
        }


        /**
         * Try to save subscriptions
         */
        try {
            $this->saveSubscriptionRepository->save();
        } catch (UnableToSaveListException $e) {
            throw new CriticalException("Unable to update subscriptions list", $e->getDebugMessage());
        }
    }

}