<?php

declare(strict_types=1);

namespace App\Application\Outbound\Override\TlsInsecure;

use App\Application\Outbound\Override\Override;
use App\Application\Outbound\Override\OverrideType;
use App\Domain\Interface\Outbound\SecurityProvider;
use App\Domain\Outbound\Collection\OutboundMap;
use App\Domain\Outbound\VO\Security\TLSSecurityVO;

/** @extends Override<OverrideTypeTlsInsecure> */
final readonly class OverrideTlsInsecure extends Override
{
    public function getTypeClass(): string
    {
        return OverrideTypeTlsInsecure::class;
    }

    public function name(): string
    {
        return 'tls-insecure';
    }

    public function createType(?string $value): OverrideType
    {
        return new OverrideTypeTlsInsecure($this->parseBool($value));
    }

    /** @param OverrideTypeTlsInsecure $type */
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
                    $security->withInsecure($type->getValue())
                )
            );
        }

        return $outbounds;
    }
}
