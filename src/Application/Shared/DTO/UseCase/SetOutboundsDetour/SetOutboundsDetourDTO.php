<?php

declare(strict_types=1);

namespace App\Application\Shared\DTO\UseCase\SetOutboundsDetour;

use App\Domain\Outbound\Collection\OutboundMap;
use App\Domain\Outbound\Entity\Outbound;

final readonly class SetOutboundsDetourDTO
{
    public function __construct(
        public OutboundMap $outbounds,
        public Outbound    $detourOutbound
    )
    {
    }
}