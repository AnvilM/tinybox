<?php

declare(strict_types=1);

namespace App\Application\Outbound\Override\TLSSNI;

use App\Application\Outbound\Override\Override;
use App\Application\Outbound\Override\OverrideType;
use App\Domain\Interface\Outbound\SecurityProvider;
use App\Domain\Outbound\Collection\OutboundMap;
use App\Domain\Outbound\VO\Security\TLSSecurityVO;

/** @extends Override<OverrideTypeTLSSNI> */
final readonly class OverrideTLSSNI extends Override
{
    public function getTypeClass(): string
    {
        return OverrideTypeTLSSNI::class;
    }

    /** @param OverrideTypeTLSSNI $type */
    protected function apply(OutboundMap $outboundMap, OverrideType $type): OutboundMap
    {
        $outbounds = $outboundMap->createEmpty();

        foreach ($outboundMap->getOutbounds() as $outbound) {
            if (!($outbound instanceof SecurityProvider)) {
                $outbounds->add($outbound);
                continue;
            }

            $security = $outbound->getSecurity();
            if (!($security instanceof TLSSecurityVO)) {
                $outbounds->add($outbound);
                continue;
            }

            $outbounds->add(
                $outbound->withSecurity(
                    $security->withServerName($type->getSni())
                )
            );
        }

        return $outbounds;
    }
}
