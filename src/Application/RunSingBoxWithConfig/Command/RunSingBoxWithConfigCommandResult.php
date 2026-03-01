<?php

declare(strict_types=1);

namespace App\Application\RunSingBoxWithConfig\Command;

final readonly class RunSingBoxWithConfigCommandResult
{
    public function __construct(
        public int $responseCode,
    )
    {
    }
}