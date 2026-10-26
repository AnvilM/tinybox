<?php

declare(strict_types=1);

namespace App\Application\Outbound\Override\VlessUUID;

use App\Application\Outbound\Override\Override;
use App\Application\Outbound\Override\OverrideType;
use App\Domain\Outbound\Collection\OutboundMap;
use App\Domain\Outbound\Entity\VlessOutbound;

/** @extends Override<OverrideTypeVlessUUID> */
final readonly class OverrideVlessUUID extends Override
{
    public function getTypeClass(): string
    {
        return OverrideTypeVlessUUID::class;
    }

    public function name(): string
    {
        return 'vless-uuid';
    }

    public function createType(?string $value): OverrideType
    {
        return new OverrideTypeVlessUUID($this->requireValue($value));
    }

    /** @param OverrideTypeVlessUUID $type */
    protected function apply(OutboundMap $outboundMap, OverrideType $type): OutboundMap
    {
        $outbounds = $outboundMap->createEmpty();

        foreach ($outboundMap->getOutbounds() as $outbound) {
            if (!($outbound instanceof VlessOutbound)) {
                $outbounds->add($outbound);
                continue;
            }

            $outbounds->add($outbound->withUUID($type->getUuid()));
        }

        return $outbounds;
    }
}
