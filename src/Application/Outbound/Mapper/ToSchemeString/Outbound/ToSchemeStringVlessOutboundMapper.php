<?php

declare(strict_types=1);

namespace App\Application\Outbound\Mapper\ToSchemeString\Outbound;

use App\Application\Outbound\Mapper\ToSchemeString\Shared\Security\ToSchemeParamsSecurityMapper;
use App\Application\Outbound\Mapper\ToSchemeString\Shared\Transport\ToSchemeParamsTransportMapper;
use App\Domain\Outbound\Entity\VlessOutbound;
use InvalidArgumentException;

final readonly class ToSchemeStringVlessOutboundMapper
{
    public function __construct(
        private ToSchemeParamsSecurityMapper  $toSchemeParamsSecurityMapper,
        private ToSchemeParamsTransportMapper $toSchemeParamsTransportMapper,
    )
    {
    }

    /**
     * @throws InvalidArgumentException
     */
    public function map(VlessOutbound $outbound): string
    {
        $string = $outbound->getType()->value . '://';
        $string .= $outbound->getUUID() . '@';
        $string .= $outbound->getServer() . ':';
        $string .= $outbound->getServerPort() . '?';
        $string .= 'flow=' . $outbound->getFlow() . '&';

        foreach (array_merge(
                     $this->toSchemeParamsSecurityMapper->map($outbound->getSecurity()),
                     $this->toSchemeParamsTransportMapper->map($outbound->getTransport())
                 ) as $param => $value) {
            $string .= $param . '=' . rawurlencode($value) . '&';
        }


        $string .= '#' . rawurlencode($outbound->getTagString());

        return $string;
    }
}