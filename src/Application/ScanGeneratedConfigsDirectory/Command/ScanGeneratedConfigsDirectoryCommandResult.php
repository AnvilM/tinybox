<?php

declare(strict_types=1);

namespace App\Application\ScanGeneratedConfigsDirectory\Command;

final readonly class ScanGeneratedConfigsDirectoryCommandResult
{
    /**
     * @param string[] $configsNames Names of configs e.g., ["config1", "config2", ...]
     */
    public function __construct(
        public array $configsNames,
    )
    {
    }
}