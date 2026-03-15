<?php

declare(strict_types=1);

namespace App\Application\AddSchemeToConfig\Command;

final readonly class AddSchemeToConfigCommand
{
    public function __construct(
        public string $name,
        public string $schemeId
    )
    {
    }
}