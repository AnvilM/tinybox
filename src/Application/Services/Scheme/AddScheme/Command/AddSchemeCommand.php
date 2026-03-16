<?php

declare(strict_types=1);

namespace App\Application\Services\Scheme\AddScheme\Command;

final readonly class AddSchemeCommand
{
    public function __construct(
        public string $schemeString
    )
    {
    }
}