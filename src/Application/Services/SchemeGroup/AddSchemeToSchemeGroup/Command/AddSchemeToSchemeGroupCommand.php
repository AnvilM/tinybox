<?php

declare(strict_types=1);

namespace App\Application\Services\SchemeGroup\AddSchemeToSchemeGroup\Command;

final readonly class AddSchemeToSchemeGroupCommand
{
    public function __construct(
        public string $name,
        public string $schemeId
    )
    {
    }
}