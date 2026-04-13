<?php

declare(strict_types=1);

namespace App\Domain\Shared\VO\Config\Subscriptions;

final readonly class SubscriptionsConfigVO
{
    public function __construct(
        public int     $timeout,
        public string  $useragent,
        public ?string $hwid
    )
    {
    }
}