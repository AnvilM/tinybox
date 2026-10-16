<?php

declare(strict_types=1);

namespace App\Application\Outbound\Override\TlsInsecure;

use App\Application\Outbound\Override\OverrideType;

final readonly class OverrideTypeTlsInsecure extends OverrideType
{
    public function __construct(private bool $value)
    {
    }

    public function getValue(): bool
    {
        return $this->value;
    }
}
