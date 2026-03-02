<?php

declare(strict_types=1);

namespace App\Application\FetchSubscriptions\Handler;

use App\Application\FetchSubscriptions\Command\FetchSubscriptionsCommand;
use App\Application\FetchSubscriptions\Fetch\FetchSubscriptions;
use App\Application\FetchSubscriptions\Mapper\SubscriptionsMapper;
use App\Core\Domain\Subscription\Collection\SubscriptionCollection;
use App\Core\Shared\Exception\CriticalException;

final readonly class FetchSubscriptionsHandler
{
    public function __construct(
        private SubscriptionsMapper $subscriptionsMapper,
        private FetchSubscriptions  $fetchSubscriptions,
    )
    {
    }

    /**
     * @return array<string, string> Raw schemes string array ["subscriptionName" => "rawSchemesString"]
     */
    public function handle(FetchSubscriptionsCommand $command): array
    {
        /**
         * Read subscription list and map to subscriptions collection
         */
        $subscriptionsCollection = $this->subscriptionsMapper->map(
            $command->subscriptionsCollectionDTO
        );


        /**
         * Check if name of specific subscription provided
         */
        if ($command->subscriptionName !== null) {
            foreach ($subscriptionsCollection as $subscription) {
                if ($subscription->name === $command->subscriptionName) return $this->fetchSubscriptions->load(
                    SubscriptionCollection::create([$subscription])
                );
            }

            /**
             * Throw exception if no subscription with specific name found
             */
            throw new CriticalException("No subscription $command->subscriptionName found");
        }

        /**
         * Fetching schemes string from subscription url
         */
        return $this->fetchSubscriptions->load(
            $subscriptionsCollection
        );

    }
}