<?php

declare(strict_types=1);

namespace App\Application\Services\Subscription\CreateSubscription\Handler;

use App\Application\Services\Subscription\CreateSubscription\Command\CreateSubscriptionCommand;
use App\Application\Shared\Subscription\Shared\File\WriteSubscriptions;
use App\Application\Shared\Subscription\UseCase\ReadSubscriptionsList\ReadSubscriptionsListUseCase;
use App\Domain\Shared\Exception\CriticalException;
use App\Domain\Shared\VO\Shared\SchemesIdsVO;
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
        private WriteSubscriptions           $writeSubscriptions
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


            /**
             * Create scheme ids
             */
            $schemeIds = new SchemesIdsVO();


            /**
             * Create new subscription
             */
            $newSubscription = new Subscription($subscriptionName, $subscriptionUrl, $schemeIds);
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
         * Try to add new subscription
         */
        try {
            $subscriptions->add($newSubscription);
        } catch (SubscriptionAlreadyExistsException) {
            throw new CriticalException ("Subscription with provided name or url already exists");
        }


        /**
         * Write subscriptions
         */
        $this->writeSubscriptions->write($subscriptions);
    }
}