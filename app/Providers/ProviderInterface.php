<?php

declare(strict_types=1);

namespace Application\Providers;

interface ProviderInterface
{
    /** @return array<string, mixed> */
    public static function register(): array;
}