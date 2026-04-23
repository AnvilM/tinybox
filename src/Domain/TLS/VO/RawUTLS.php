<?php

declare(strict_types=1);

namespace App\Domain\TLS\VO;

final readonly class RawUTLS
{
    public function __construct(
        public ?string $fingerprint,
        public bool    $enabled)
    {
    }
}