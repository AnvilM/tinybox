<?php

declare(strict_types=1);

namespace App\Domain\Shared\Ports\OutboundTest\OutboundCountyCode;

use App\Domain\Outbound\Collection\OutboundMap;
use Psl\Async\Exception\CompositeException;
use Psl\Collection\MutableMap;

interface OutboundCountyCodePort
{
    /**
     * Get outbounds ip country codes
     *
     * @param OutboundMap $outboundsMap Map of outbounds to fetch ip
     * @param bool $outboundIpFallback Use default outbound ip if unable to get real ip
     *
     * @return MutableMap<string, string> Mutable map of tag => code
     *
     * @throws CompositeException If unable to get ip
     */
    public function getCountryCodes(OutboundMap $outboundsMap, bool $outboundIpFallback): MutableMap;
}