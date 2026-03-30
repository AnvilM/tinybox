<?php

declare(strict_types=1);

namespace App\Application\Services\Subscription\CreateSubscription\Handler;

use App\Application\Services\Subscription\CreateSubscription\Command\CreateSubscriptionCommand;
use App\Application\Shared\Subscription\Exception\UseCase\FetchSchemes\NoValidSchemesFoundException;
use App\Application\Shared\Subscription\Shared\File\WriteSubscriptions;
use App\Application\Shared\Subscription\Shared\UseCase\ReadSubscriptionsList\ReadSubscriptionsListUseCase;
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
        private ReadSubscriptionsListUseCase $readSubscriptionsListUseCase,
        private WriteSubscriptions           $writeSubscriptions,
        private FetchSchemesUseCase          $fetchSchemesUseCase,
    )
    {
    }

    /**
     * @throws CriticalException
     */
    public function handle(CreateSubscriptionCommand $command): void
    {
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
         * Read subscription list
         */
        $subscriptions = $this->readSubscriptionsListUseCase->handle();


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
         * Try to add new subscription
         */
        try {
            $subscriptions->add(new Subscription(
                $subscriptionName,
                $subscriptionUrl,
                $schemes
            ));
        } catch (SubscriptionAlreadyExistsException) {
            throw new CriticalException ("Subscription with provided name or url already exists");
        }

        /**
         * Write subscriptions
         */
        $this->writeSubscriptions->write($subscriptions);
    }
}