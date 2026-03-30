<?php

declare(strict_types=1);

namespace App\Application\Shared\Utils\OutboundTest\GetIpCountyCode\SingBox;

use App\Application\Shared\Utils\OutboundTest\GetIpCountyCode\SingBox\Process\DTO\OutboundIpDTO;
use App\Application\Shared\Utils\OutboundTest\GetIpCountyCode\SingBox\Process\SingBoxFetchIp;
use App\Domain\Outbound\Collection\OutboundMap;
use App\Domain\Shared\Exception\CriticalException;
use Psl\Collection\MutableVector;

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
     * @return MutableVector<OutboundIpDTO> Mutable map of OutboundIpDTO
     *
     * @throws CriticalException
     */
    public function getOutboundIp(OutboundMap $outboundsMap): MutableVector
    {
        return $this->singBoxFetchIp->fetchIp($outboundsMap);
    }
}