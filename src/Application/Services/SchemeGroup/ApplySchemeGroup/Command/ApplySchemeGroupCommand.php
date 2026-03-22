<?php

declare(strict_types=1);

namespace App\Application\Services\SchemeGroup\ApplySchemeGroup\Command;

final readonly class ApplySchemeGroupCommand
{
    public function __construct(
        public string $schemeGroupName
    )
    {
    }
}