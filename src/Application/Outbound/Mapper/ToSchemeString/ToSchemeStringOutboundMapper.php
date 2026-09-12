<?php

declare(strict_types=1);

namespace App\Application\Outbound\Mapper\ToSchemeString;

use App\Application\Outbound\Mapper\ToSchemeString\Outbound\ToSchemeStringShadowsocksOutboundMapper;
use App\Application\Outbound\Mapper\ToSchemeString\Outbound\ToSchemeStringVlessOutboundMapper;
use App\Domain\Outbound\Entity\Outbound;
use App\Domain\Outbound\Entity\ShadowsocksOutbound;
use App\Domain\Outbound\Entity\VlessOutbound;
use InvalidArgumentException;

final readonly class ToSchemeStringOutboundMapper
{
    public function __construct(
        private ToSchemeStringShadowsocksOutboundMapper $toSchemeStringShadowsocksOutboundMapper,
        private ToSchemeStringVlessOutboundMapper       $toSchemeStringVlessOutboundMapper,
    )
    {
    }

    /**
     * @throws InvalidArgumentException
     */
    public function map(Outbound $outbound): string
    {
        if ($outbound instanceof VlessOutbound) {
            return $this->toSchemeStringVlessOutboundMapper->map($outbound);
        }

        if ($outbound instanceof ShadowsocksOutbound) {
            return $this->toSchemeStringShadowsocksOutboundMapper->map($outbound);
        }

        throw new InvalidArgumentException();
    }
}