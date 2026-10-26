<?php

declare(strict_types=1);

namespace App\Application\Outbound\DTO\UseCase\ApplyOverride;

use App\Application\Outbound\Override\OverrideDefinition;
use App\Domain\Outbound\Collection\OutboundMap;

final readonly class ApplyOverrideDTO
{
    /**
     * @param OutboundMap $outbounds Current set of the pipeline
     * @param OverrideDefinition $definition Rule to apply
     */
    public function __construct(
        public OutboundMap        $outbounds,
        public OverrideDefinition $definition,
    )
    {
    }
}
