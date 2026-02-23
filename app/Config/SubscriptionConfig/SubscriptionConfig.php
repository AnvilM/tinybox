<?php

declare(strict_types=1);

namespace Application\Config\SubscriptionConfig;

use Application\Config\ApplicationConfig\ApplicationConfig;
use RuntimeException;

final readonly class SubscriptionConfig
{
    /**
     * @throws RuntimeException
     */
    public static function subscriptionListPath(): string
    {
        return ApplicationConfig::dataHomeDirectoryPath() . '/subscriptions.json';
    }
}