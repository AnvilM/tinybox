<?php

declare(strict_types=1);

namespace App\Domain\Outbound\VO\Transport;

use App\Domain\Interface\Shared\Equable;

abstract readonly class TransportVO implements Equable
{
    public function equals(mixed $other): bool
    {
        return $other instanceof static;
    }

}