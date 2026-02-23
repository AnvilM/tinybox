<?php

declare(strict_types=1);

namespace App\Application\UpdateSubscriptions\Handler;

use App\Application\UpdateSubscriptions\Command\UpdateSubscriptionsCommand;

final readonly class UpdateSubscriptionsHandler
{
    public function __construct(
        private GetSubscriptions $getSubscriptions,
        private GetSchemes       $getSchemes,
    )
    {
    }

    public function handle(UpdateSubscriptionsCommand $command): void
    {
        $subscriptionCollection = $this->getSubscriptions->get(
            $command->subscriptionName
        );

        $schemeMap = $this->getSchemes->get($subscriptionCollection);
    }
}