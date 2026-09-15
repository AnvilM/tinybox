<?php

declare(strict_types=1);

namespace App\Application\Outbound\DTO\UseCase\OverrideOutbounds;

use App\Domain\Outbound\Collection\OutboundMap;
use App\Domain\Shared\VO\Shared\NonEmptyStringVO;

final readonly class OverrideOutboundDTO
{
    public function __construct(
        public OutboundMap       $outboundMap,
        public ?NonEmptyStringVO $vlessUUID = null,
        public ?NonEmptyStringVO $ssPass = null,
    )
    {
    }
}