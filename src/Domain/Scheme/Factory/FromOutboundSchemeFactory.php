<?php

declare(strict_types=1);

namespace App\Domain\Scheme\Factory;

use App\Domain\Outbound\Entity\Outbound;
use App\Domain\Outbound\Entity\ShadowsocksOutbound;
use App\Domain\Outbound\Entity\VlessOutbound;
use App\Domain\Outbound\Exception\UnsupportedOutboundTypeException;
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

final readonly class FromOutboundSchemeFactory
{
    /**
     * Creates a Scheme entity from outbound
     *
     * @param Outbound $outbound Outbound
     *
     * @return Scheme Created Scheme entity
     *
     * @throws InvalidArgumentException If required fields are missing
     * @throws UnsupportedOutboundTypeException If outbound type is unsupported
     */
    public static function fromOutbound(Outbound $outbound): Scheme
    {

        if ($outbound instanceof VlessOutbound) {
            return self::vlessScheme($outbound);
        }

        if ($outbound instanceof ShadowsocksOutbound) {
            return self::shadowsocksScheme($outbound);
        }

        throw new UnsupportedOutboundTypeException($outbound->getType()->value);
    }


    /**
     * Creates a Vless scheme entity from outbound
     *
     * @param VlessOutbound $outbound Outbound
     *
     * @return VlessScheme Created vless scheme entity
     *
     * @throws InvalidArgumentException If provided invalid fields
     */
    private static function vlessScheme(VlessOutbound $outbound): VlessScheme
    {
        try {
            $security = $outbound->getSecurity();
            $transport = $outbound->getTransport();

            return new VlessScheme(
                new NonEmptyStringVO($outbound->getId()),
                new NonEmptyStringVO($outbound->getServer()),
                new PortVO($outbound->getServerPort()),
                new NonEmptyStringVO($security?->getServerName()),
                new NonEmptyStringVO($security instanceof RealitySecurityVO ? $security->getPublicKey() : null),
                $security?->getShortId(),
                $outbound->getTag(),
                $outbound->getFlow(),
                $security?->getFingerprint() === null ? null : new NonEmptyStringVO($security?->getFingerprint()->value),
                null,
                $security instanceof RealitySecurityVO ? SchemeSecurityVO::Reality : SchemeSecurityVO::TLS,
                $transport === null ? null : ($transport instanceof WebSocketTransportVO ? $transport->getPath() : null),
                $transport === null ? null : ($transport instanceof WebSocketTransportVO ? $transport->getHost() : null),
            );
        } catch (ValueError) {
            throw new InvalidArgumentException();
        }
    }


    /**
     * Creates a shadowsocks scheme entity from outbound
     *
     * @param ShadowsocksOutbound $outbound Outbound
     *
     * @return ShadowsocksScheme Created shadowsocks scheme entity
     *
     * @throws InvalidArgumentException If required fields are missing or provided invalid fields
     */
    private static function shadowsocksScheme(ShadowsocksOutbound $outbound): ShadowsocksScheme
    {
        $transport = $outbound->getTransport();

        return new ShadowsocksScheme(
            $outbound->getTag(),
            $outbound->getUserinfo(),
            $outbound->getPlugin(),
            new NonEmptyStringVO($outbound->getServer()),
            new PortVO($outbound->getServerPort()),
            $outbound->getTransport() === null ? null : TransportTypeVO::WebSocket,
            $transport === null ? null : ($transport instanceof WebSocketTransportVO ? $transport->getPath() : null),
            $transport === null ? null : ($transport instanceof WebSocketTransportVO ? $transport->getHost() : null),
        );
    }
}