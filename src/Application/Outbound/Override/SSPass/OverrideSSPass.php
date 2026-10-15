<?php

declare(strict_types=1);

namespace App\Application\Outbound\Override\SSPass;

use App\Application\Outbound\Override\Override;
use App\Application\Outbound\Override\OverrideType;
use App\Domain\Outbound\Collection\OutboundMap;
use App\Domain\Outbound\Entity\ShadowsocksOutbound;

/** @extends Override<OverrideTypeSSPass> */
final readonly class OverrideSSPass extends Override
{
    public function getTypeClass(): string
    {
        return OverrideTypeSSPass::class;
    }

    /** @param OverrideTypeSSPass $type */
    protected function apply(OutboundMap $outboundMap, OverrideType $type): OutboundMap
    {
        $outbounds = $outboundMap->createEmpty();

        foreach ($outboundMap->getOutbounds() as $outbound) {
            if (!($outbound instanceof ShadowsocksOutbound)) {
                $outbounds->add($outbound);
                continue;
            }

            $outbounds->add($outbound->withPassword($type->getPassword()));
        }

        return $outbounds;
    }
}
