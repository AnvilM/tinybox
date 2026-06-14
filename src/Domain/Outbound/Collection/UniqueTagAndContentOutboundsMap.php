<?php

declare(strict_types=1);

namespace App\Domain\Outbound\Collection;

use App\Domain\Outbound\Entity\Outbound;
use Override;

final readonly class UniqueTagAndContentOutboundsMap extends UniqueTagOutboundsMap
{
    /**
     * @inheritDoc
     */
    #[Override]
    public function getDuplicate(Outbound $outbound): ?Outbound
    {
        foreach ($this->outbounds->toArray() as $outboundItem) {
            if ($outboundItem->equalsContent($outbound)) return $outboundItem;
        }

        return null;
    }
}