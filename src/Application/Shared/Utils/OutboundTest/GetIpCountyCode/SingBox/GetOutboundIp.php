<?php

declare(strict_types=1);

namespace App\Application\Shared\Utils\OutboundTest\GetIpCountyCode\SingBox;

use App\Application\Shared\Utils\OutboundTest\GetIpCountyCode\SingBox\Process\DTO\OutboundIpDTO;
use App\Application\Shared\Utils\OutboundTest\GetIpCountyCode\SingBox\Process\SingBoxFetchIp;
use App\Domain\Outbound\Collection\OutboundMap;
use App\Domain\Shared\Exception\CriticalException;
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
     * @throws CriticalException
     */
    public function getOutboundIp(OutboundMap $outboundsMap): Vector
    {
        return $this->singBoxFetchIp->fetchIp($outboundsMap);
    }
}