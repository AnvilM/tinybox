<?php

declare(strict_types=1);

namespace App\Domain\Outbound\VO\RawOutbound;

readonly class RawOutboundVO
{
    public function __construct(public string $type, public string $tag)
    {
    }
}