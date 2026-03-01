<?php

declare(strict_types=1);

namespace App\Application\FetchSubscriptions\Handler;

use App\Application\FetchSubscriptions\Command\UpdateSubscriptionsCommand;
use App\Application\FetchSubscriptions\Fetch\FetchSubscriptions;

final readonly class FetchSubscriptionsHandler
{
    public function __construct(
        private GetSubscriptions   $getSubscriptions,
        private FetchSubscriptions $fetchSubscriptions,
    )
    {
    }

    /**
     * @return array<string, string> Raw schemes string array ["subscriptionName" => "rawSchemesString"]
     */
    public function handle(UpdateSubscriptionsCommand $command): array
    {
        $subscriptionCollection = $this->getSubscriptions->get(
            $command->subscriptionName
        );

        /**
         * Fetching schemes string from subscription url
         */
        return $this->fetchSubscriptions->load(
            $subscriptionCollection
        );

//        $schemeMap = $this->getSchemes->get($subscriptionCollection);
//
//        $this->createSingBoxConfig->create($schemeMap);
    }
}