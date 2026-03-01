<?php

declare(strict_types=1);

namespace App\Application\GenerateConfigs\Command;

final readonly class GenerateConfigsCommand
{
    /**
     * @param array<string, string> $rawSchemesArray Array of raw schemes strings e.g., ["subscriptionName" => "RawSchemesString", ...]
     */
    public function __construct(
        public array $rawSchemesArray
    )
    {
    }
}