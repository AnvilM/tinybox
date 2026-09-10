<?php

declare(strict_types=1);

namespace App\Domain\Outbound\Factory\FromScheme;

use App\Domain\Outbound\Entity\Outbound;
use App\Domain\Outbound\Entity\ShadowsocksOutbound;
use App\Domain\Outbound\Entity\VlessOutbound;
use App\Domain\Outbound\Exception\UnsupportedOutboundTypeException;
use App\Domain\Outbound\VO\Security\FingerprintVO;
use App\Domain\Scheme\Entity\Scheme;
use App\Domain\Scheme\Entity\ShadowsocksScheme;
use App\Domain\Scheme\Entity\VlessScheme;
use App\Domain\Scheme\VO\SchemeSecurityVO;
use App\Domain\Shared\VO\Outbound\Security\RealitySecurityVO;
use App\Domain\Shared\VO\Outbound\Transport\TransportTypeVO;
use App\Domain\Shared\VO\Outbound\Transport\WebSocketTransportVO;
use App\Domain\Shared\VO\Shared\NonEmptyStringVO;
use App\Domain\Shared\VO\Shared\PortVO;
use InvalidArgumentException;

final readonly class FromSchemeOutboundFactory
{
    /**
     * Creates an Outbound entity from Scheme entity
     *
     * @param Scheme $scheme Scheme entity
     *
     * @return Outbound The created Outbound entity
     *
     * @throws InvalidArgumentException If required fields are missing or empty
     * @throws UnsupportedOutboundTypeException If outbound type is unsupported
     */
    public static function fromScheme(Scheme $scheme): Outbound
    {
        if ($scheme instanceof VlessScheme) {
            return self::vlessOutbound($scheme);
        }

        if ($scheme instanceof ShadowsocksScheme) {
            return self::shadowsocksOutbound($scheme);
        }

        throw new UnsupportedOutboundTypeException($scheme->getType()->value);
    }

    /**
     * Creates a Vless outbound entity from Vless scheme entity
     *
     * @param VlessScheme $scheme Vless scheme entity
     *
     * @return VlessOutbound The created Vless outbound entity
     *
     * @throws InvalidArgumentException If required fields are missing or empty
     */
    private static function vlessOutbound(VlessScheme $scheme): VlessOutbound
    {
        return new VlessOutbound(
            new NonEmptyStringVO($scheme->getTagString()),
            new NonEmptyStringVO($scheme->getServer()),
            new PortVO($scheme->getServerPort()),
            new NonEmptyStringVO($scheme->getUuid()),
            $scheme->getFlow() === null ? null : new NonEmptyStringVO($scheme->getFlow()),
            $scheme->getSecurity() === null ? null : ($scheme->getSecurity() === SchemeSecurityVO::Reality ? new RealitySecurityVO(
                new NonEmptyStringVO($scheme->getPbk()),
                $scheme->getSid() === null ? null : new NonEmptyStringVO($scheme->getSid()),
                new NonEmptyStringVO($scheme->getSni()),
                $scheme->getFp() === null ? null : FingerprintVO::from($scheme->getFp()),
            ) : null),
            $scheme->getTransportType() === null ? null
                : ($scheme->getTransportType() === TransportTypeVO::WebSocket ? new WebSocketTransportVO(
                new NonEmptyStringVO($scheme->getPath()),
                new NonEmptyStringVO($scheme->getHost())
            ) : null),
        );
    }

    /**
     * Creates a shadowsocks outbound entity from Vless scheme entity
     *
     * @param ShadowsocksScheme $scheme shadowsocks scheme entity
     *
     * @return ShadowsocksOutbound The created shadowsocks outbound entity
     *
     * @throws InvalidArgumentException If required fields are missing or empty
     */
    private static function shadowsocksOutbound(ShadowsocksScheme $scheme): ShadowsocksOutbound
    {
        return new ShadowsocksOutbound(
            new NonEmptyStringVO($scheme->getTagString()),
            new NonEmptyStringVO($scheme->getServer()),
            new PortVO($scheme->getServerPort()),
            $scheme->getUserinfo(),
            $scheme->getPlugin(),
            $scheme->getTransport() === null ? null
                : ($scheme->getTransport() === TransportTypeVO::WebSocket ? new WebSocketTransportVO(
                new NonEmptyStringVO($scheme->getPath()),
                new NonEmptyStringVO($scheme->getHost())
            ) : null),
        );
    }
}