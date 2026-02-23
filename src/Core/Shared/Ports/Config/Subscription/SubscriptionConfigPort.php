<?php

declare(strict_types=1);

namespace App\Core\Shared\Ports\Config\Subscription;

use App\Core\Shared\Exception\CriticalException;

interface SubscriptionConfigPort
{
    /**
     * Subscription list path from config
     *
     * @return string Subscription list path
     *
     * @throws CriticalException Cannot find subscriptions list path
     */
    public static function subscriptionListPath(): string;
}