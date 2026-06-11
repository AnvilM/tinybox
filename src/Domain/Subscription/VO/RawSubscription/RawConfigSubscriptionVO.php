<?php

declare(strict_types=1);

namespace App\Domain\Subscription\VO\RawSubscription;

final readonly class RawConfigSubscriptionVO extends RawSubscriptionVO
{
    public function __construct(
        string        $name,
        string        $url,
        string        $type,
        public string $config
    )
    {
        parent::__construct($name, $url, $type);
    }
}