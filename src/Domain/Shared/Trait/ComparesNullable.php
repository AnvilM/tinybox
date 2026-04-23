<?php

declare(strict_types=1);

namespace App\Domain\Shared\Trait;

use App\Domain\Interface\Shared\Equable;

trait ComparesNullable
{
    protected function equalsNullable(?Equable $a, ?Equable $b): bool
    {
        if ($a === null && $b === null) return true;
        if ($a === null || $b === null) return false;

        return $a->equals($b);
    }
}