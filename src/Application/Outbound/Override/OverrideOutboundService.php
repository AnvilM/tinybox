<?php

declare(strict_types=1);

namespace App\Application\Outbound\Override;

use App\Domain\Outbound\Collection\OutboundMap;
use Psl\Collection\Vector;

final readonly class OverrideOutboundService
{
    /**
     * @template T of OutboundMap
     *
     * @param T $outbounds Outbounds to override
     * @param Vector<Override> $overrides Overrides
     *
     * @return T Outbounds map with overridden outbounds
     */
    public function override(OutboundMap $outbounds, Vector $overrides): OutboundMap
    {
        if ($overrides->isEmpty()) return $outbounds;


        $outboundsMap = $outbounds->createEmpty();

        foreach ($outbounds->getOutbounds() as $outbound) {
            foreach ($overrides as $override) {
                $outbound = $override->override($outbound);
            }

            $outboundsMap->add($outbound);
        }

        return $outboundsMap;
    }
}