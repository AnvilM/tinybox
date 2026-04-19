<?php

declare(strict_types=1);

namespace App\Domain\Interface\Shared;

interface Equable
{
    public function equals(mixed $other): bool;
}