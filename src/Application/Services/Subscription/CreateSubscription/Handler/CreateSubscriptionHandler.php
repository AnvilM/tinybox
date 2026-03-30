<?php

declare(strict_types=1);

namespace App\Application\Services\Subscription\CreateSubscription\Handler;

use App\Application\Exception\Repository\Shared\UnableToGetListException;
use App\Application\Exception\Repository\Shared\UnableToSaveListException;
use App\Application\Repository\Subscription\AddSubscriptionRepository;
use App\Application\Repository\Subscription\GetSubscriptionListRepository;
use App\Application\Services\Subscription\CreateSubscription\Command\CreateSubscriptionCommand;
use App\Application\Shared\Subscription\Exception\UseCase\FetchSchemes\NoValidSchemesFoundException;
use App\Application\Shared\Subscription\UseCase\FetchSchemes\FetchSchemesUseCase;
use App\Domain\Shared\Exception\CriticalException;
use App\Domain\Subscription\Entity\Subscription;
use App\Domain\Subscription\Exception\InvalidSubscriptionNameException;
use App\Domain\Subscription\Exception\InvalidSubscriptionURLException;
use App\Domain\Subscription\Exception\SubscriptionAlreadyExistsException;
use App\Domain\Subscription\VO\SubscriptionNameVO;
use App\Domain\Subscription\VO\SubscriptionURLVO;

final readonly class CreateSubscriptionHandler
{
    public function __construct(
        private GetSubscriptionListRepository $getSubscriptionListRepository,
        private FetchSchemesUseCase           $fetchSchemesUseCase,
        private AddSubscriptionRepository     $addSubscriptionRepository,
    )
    {
    }

    /**
     * @throws CriticalException
     */
    public function handle(CreateSubscriptionCommand $command): void
    {
        /**
         * Try to create subscription name and subscription URL
         */
        try {
            /**
             * Create subscription name
             */
            $subscriptionName = new SubscriptionNameVO($command->name);


            /**
             * Create subscription url
             */
            $subscriptionUrl = new SubscriptionUrlVO($command->url);
        } catch (InvalidSubscriptionNameException|InvalidSubscriptionURLException $e) {
            throw new CriticalException($e instanceof InvalidSubscriptionNameException
                ? "Invalid subscription name provided"
                : "Invalid subscription url provided"
            );
        }


        /**
         * Try to read subscription list
         */
        try {
            $subscriptions = $this->getSubscriptionListRepository->getSubscriptionsList();
        } catch (UnableToGetListException $e) {
            throw new CriticalException ("Unable to add subscription: " . $e->getMessage(), $e->getDebugMessage());
        }


        /**
         * Check subscription with provided name or url already exists
         */
        if ($subscriptions->containsSubscriptionUrlOrName($subscriptionUrl, $subscriptionName))
            throw new CriticalException("Subscription with name {$subscriptionName->getName()} or url {$subscriptionUrl->getUrl()} already exists");


        /**
         * Try to fetch subscription schemes
         */
        try {
            $schemes = $this->fetchSchemesUseCase->handle($subscriptionUrl);
        } catch (NoValidSchemesFoundException) {
            throw new CriticalException ("No valid schemes found", $subscriptionUrl->getUrl());
        }


        /**
         * Try to add new subscription and save subscriptions list
         */
        try {
            $this->addSubscriptionRepository->add(new Subscription(
                $subscriptionName,
                $subscriptionUrl,
                $schemes
            ))->save();
        } catch (SubscriptionAlreadyExistsException) {
            throw new CriticalException ("Subscription with provided name or url already exists");
        } catch (UnableToSaveListException|UnableToGetListException $e) {
            throw new CriticalException ("Unable to add subscription: " . $e->getMessage(), $e->getDebugMessage());
        }
    }
}