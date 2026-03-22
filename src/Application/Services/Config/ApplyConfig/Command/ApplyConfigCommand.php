<?php

declare(strict_types=1);

namespace App\Application\Services\Config\ApplyConfig\Command;

final readonly class ApplyConfigCommand
{
    public function __construct(
        public string $configName
    )
    {
    }
}