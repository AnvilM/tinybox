<?php

declare(strict_types=1);

namespace App\Application\Shared\DTO\UseCase\OutboundsLatency;

use App\Domain\Outbound\Collection\OutboundMap;

final readonly class OutboundsLatencyDTO
{
    public function __construct(
        public OutboundMap $outbounds,
        public ?string     $method,
    )
    {
    }
}