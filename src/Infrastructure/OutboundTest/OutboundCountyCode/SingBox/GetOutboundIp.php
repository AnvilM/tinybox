<?php

declare(strict_types=1);

namespace App\Infrastructure\OutboundTest\OutboundCountyCode\SingBox;

use App\Domain\Outbound\Collection\OutboundMap;
use App\Infrastructure\OutboundTest\OutboundCountyCode\SingBox\Process\DTO\OutboundIpDTO;
use App\Infrastructure\OutboundTest\OutboundCountyCode\SingBox\Process\SingBoxFetchIp;
use Psl\Async\Exception\CompositeException;
use Psl\Collection\Vector;

final readonly class GetOutboundIp
{
    public function __construct(
        private SingBoxFetchIp $singBoxFetchIp
    )
    {
    }


    /**
     * @param OutboundMap $outboundsMap Map of outbounds to fetch ip
     *
     * @return Vector<OutboundIpDTO> Vector of OutboundIpDTO
     *
     * @throws CompositeException If unable to get ip
     */
    public function getOutboundIp(OutboundMap $outboundsMap): Vector
    {
        return $this->singBoxFetchIp->fetchIp($outboundsMap);
    }
}