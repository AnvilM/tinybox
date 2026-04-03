<?php

declare(strict_types=1);

namespace App\Application\DTO\Outbound;

use App\Domain\Outbound\Entity\Outbound;

final readonly class OutboundLatencyDTO
{
    /**
     * @param Outbound $outbound Outbound
     * @param int|null $latency Latency in ms
     */
    public function __construct(
        public Outbound $outbound,
        public ?int     $latency
    )
    {
    }
}