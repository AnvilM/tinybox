<?php

declare(strict_types=1);

namespace App\Domain\Outbound\Factory;

use App\Domain\Outbound\DTO\RawOutboundDTO;
use App\Domain\Outbound\Entity\Outbound;
use App\Domain\Outbound\Exception\UnsupportedProtocolException;
use App\Domain\Outbound\Exception\UnsupportedSecurityException;
use App\Domain\Outbound\Exception\UnsupportedTransportException;
use App\Domain\Outbound\Factory\FromRawOutboundFactory\FromRawOutboundOutboundFactory;
use InvalidArgumentException;

final readonly class OutboundFactory
{
    /**
     * Creates an Outbound entity from Raw outbound dto
     *
     * @param RawOutboundDTO $rawOutbound Raw outbound dto
     *
     * @return Outbound The created Outbound entity
     *
     * @throws UnsupportedProtocolException
     * @throws UnsupportedSecurityException
     * @throws UnsupportedTransportException
     * @throws InvalidArgumentException
     */
    public static function fromRawOutbound(RawOutboundDTO $rawOutbound): Outbound
    {
        return FromRawOutboundOutboundFactory::create($rawOutbound);
    }
}