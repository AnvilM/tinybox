<?php

declare(strict_types=1);

namespace App\Domain\Outbound\Factory\FromRawOutboundFactory;

use App\Domain\Outbound\Entity\Outbound;
use App\Domain\Outbound\Exception\UnsupportedProtocolException;
use App\Domain\Outbound\Exception\UnsupportedSecurityException;
use App\Domain\Outbound\Exception\UnsupportedTransportException;
use App\Domain\Outbound\Factory\FromRawOutboundFactory\Outbound\FromRawOutboundShadowsocksOutboundFactory;
use App\Domain\Outbound\Factory\FromRawOutboundFactory\Outbound\FromRawOutboundVlessOutboundFactory;
use App\Domain\Outbound\VO\ProtocolVO;
use App\Domain\Outbound\VO\RawOutboundVO;
use App\Domain\Shared\Ports\UUID\UUIDPort;
use App\Domain\Shared\VO\Shared\NonEmptyStringVO;
use InvalidArgumentException;

final readonly class FromRawOutboundOutboundFactory
{
    public function __construct(
        private FromRawOutboundVlessOutboundFactory       $fromRawOutboundVlessOutboundFactory,
        private FromRawOutboundShadowsocksOutboundFactory $fromRawOutboundShadowsocksOutboundFactory,
        private UUIDPort                                  $uuidPort,
    )
    {
    }

    /**
     * @throws UnsupportedProtocolException
     * @throws UnsupportedTransportException
     * @throws InvalidArgumentException
     * @throws UnsupportedSecurityException
     */
    public function create(RawOutboundVO $rawOutbound, ?string $id): Outbound
    {
        $id === null ? $id = $this->uuidPort->generateNonEmptyString() : $id = new NonEmptyStringVO($id);


        return match (ProtocolVO::tryFromAlias($rawOutbound->protocol)) {
            ProtocolVO::Vless => $this->fromRawOutboundVlessOutboundFactory->create($rawOutbound, $id),
            ProtocolVO::Shadowsocks => $this->fromRawOutboundShadowsocksOutboundFactory->create($rawOutbound, $id),
            default => throw new UnsupportedProtocolException($rawOutbound->protocol)
        };
    }
}