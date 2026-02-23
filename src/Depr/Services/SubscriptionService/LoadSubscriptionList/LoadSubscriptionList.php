<?php

declare(strict_types=1);

namespace App\Core\Services\SubscriptionService\LoadSubscriptionList;

use App\Core\Depr\Helper\File;
use App\Core\Exceptions\ApplicationException;
use App\Core\Services\SubscriptionService\LoadSubscriptionList\Validation\ValidateSubscriptionList;
use App\Infrastructure\Shared\CLI\Output;
use Application\Config\SubscriptionConfig\SubscriptionConfig;

final readonly class LoadSubscriptionList
{
    public function __construct(
        private ValidateSubscriptionList $validateAppConfiguration
    )
    {
    }

    /**
     * Load and validate config file
     *
     * @return array<string, string>
     * @throws ApplicationException
     */
    public function loadSubscriptionList(): array
    {
        $subscriptionList = File::loadJson(
            SubscriptionConfig::subscriptionListPath(),
            "subscription list"
        );

        $this->validateAppConfiguration->validateSubscriptionList($subscriptionList);

        Output::out("<green>[✓] Subscription list successfully loaded</green>")->br();

        // TODO: add no subscriptions found error

        /** @var array<string, string> */
        return $subscriptionList;
    }
}