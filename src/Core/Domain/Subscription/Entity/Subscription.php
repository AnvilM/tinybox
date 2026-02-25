<?php

declare(strict_types=1);

namespace App\Core\Domain\Subscription\Entity;

final readonly class Subscription
{
    public function __construct(
        public string $name,
        public string $url,
    )
    {
    }
}