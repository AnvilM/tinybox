<?php

declare(strict_types=1);

namespace App\Application\RunSingBox\Command;

final readonly class RunSingBoxCommand
{
    public function __construct(
        public string $subscriptionName,
        public bool   $isSystemCtl
    )
    {
    }
}