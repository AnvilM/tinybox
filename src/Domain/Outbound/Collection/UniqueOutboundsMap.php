<?php

declare(strict_types=1);

namespace App\Domain\Outbound\Collection;

use App\Domain\Outbound\Entity\Outbound;
use Override;

final readonly class UniqueOutboundsMap extends OutboundMap
{
    /**
     * Check outbound tag unique in map
     *
     * @param Outbound $outbound Outbound
     *
     * @return bool Returns true if not unique
     */
    #[Override]
    public function containsOutbound(Outbound $outbound): bool
    {
        return parent::containsOutbound($outbound) ||
            array_any(
                $this->outbounds->toArray(),
                fn($outboundItem) => $outboundItem->getTagString() === $outbound->getTagString()
            );
    }
}