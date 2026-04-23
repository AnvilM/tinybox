<?php

declare(strict_types=1);

namespace App\Domain\TLS\VO;

final readonly class RawReality
{
    public function __construct(
        public ?string $publicKey,
        public ?string $shortId,
        public bool    $enabled)
    {
    }
}