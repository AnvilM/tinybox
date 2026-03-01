<?php

declare(strict_types=1);

namespace App\Application\RunSingBoxWithConfig\Command;

final readonly class RunSingBoxWithConfigCommand
{
    public function __construct(
        public ?string $subscriptionName,
    )
    {
    }
}