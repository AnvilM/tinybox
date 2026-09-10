<?php

declare(strict_types=1);

namespace App\Domain\Outbound\Factory\FromRawOutboundFactory;

use App\Domain\Outbound\DTO\RawOutboundDTO;
use App\Domain\Outbound\Entity\Outbound;
use App\Domain\Outbound\Exception\UnsupportedProtocolException;
use App\Domain\Outbound\Exception\UnsupportedSecurityException;
use App\Domain\Outbound\Exception\UnsupportedTransportException;
use App\Domain\Outbound\Factory\FromRawOutboundFactory\Outbound\FromRawOutboundShadowsocksOutboundFactory;
use App\Domain\Outbound\Factory\FromRawOutboundFactory\Outbound\FromRawOutboundVlessOutboundFactory;
use App\Domain\Outbound\VO\ProtocolVO;
use InvalidArgumentException;

final readonly class FromRawOutboundOutboundFactory
{
    /**
     * @throws UnsupportedProtocolException
     * @throws UnsupportedTransportException
     * @throws InvalidArgumentException
     * @throws UnsupportedSecurityException
     */
    public static function create(RawOutboundDTO $rawOutbound): Outbound
    {
        return match (ProtocolVO::tryFromAlias($rawOutbound->protocol)) {
            ProtocolVO::Vless => FromRawOutboundVlessOutboundFactory::create($rawOutbound),
            ProtocolVO::Shadowsocks => FromRawOutboundShadowsocksOutboundFactory::create($rawOutbound),
            default => throw new UnsupportedProtocolException($rawOutbound->protocol)
        };
    }
}