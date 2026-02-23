<?php

declare(strict_types=1);

namespace App\Infrastructure\Shared\Config;

use App\Core\Shared\Exception\CriticalException;
use App\Core\Shared\Ports\Config\Subscription\SubscriptionConfigPort;
use Application\Config\SubscriptionConfig\SubscriptionConfig as Config;
use RuntimeException;

final readonly class SubscriptionConfig implements SubscriptionConfigPort
{

    public static function subscriptionListPath(): string
    {
        try {
            return Config::subscriptionListPath();
        } catch (RuntimeException $exception) {
            throw new CriticalException("Unable to find subscription list path", $exception->getMessage());
        }
    }
}