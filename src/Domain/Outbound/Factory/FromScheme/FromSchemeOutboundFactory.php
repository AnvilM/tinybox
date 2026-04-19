<?php

declare(strict_types=1);

namespace App\Domain\Outbound\Factory\FromScheme;

use App\Domain\Outbound\Entity\Outbound;
use App\Domain\Outbound\Entity\ShadowsocksOutbound;
use App\Domain\Outbound\Entity\TLS\Reality;
use App\Domain\Outbound\Entity\TLS\TLS;
use App\Domain\Outbound\Entity\TLS\UTLS;
use App\Domain\Outbound\Entity\VlessOutbound;
use App\Domain\Outbound\Exception\UnsupportedOutboundTypeException;
use App\Domain\Scheme\Entity\Scheme;
use App\Domain\Scheme\Entity\ShadowsocksScheme;
use App\Domain\Scheme\Entity\VlessScheme;
use App\Domain\Shared\VO\Shared\NonEmptyStringVO;
use App\Domain\Shared\VO\Shared\PortVO;
use InvalidArgumentException;

final readonly class FromSchemeOutboundFactory
{
    /**
     * Creates an Outbound entity from Scheme entity
     *
     * @param Scheme $scheme Scheme entity
     * @param int $id Outbound id
     *
     * @return Outbound The created Outbound entity
     *
     * @throws InvalidArgumentException If required fields are missing or empty
     * @throws UnsupportedOutboundTypeException If outbound type is unsupported
     */
    public static function fromScheme(Scheme $scheme, int $id): Outbound
    {
        if ($scheme instanceof VlessScheme) {
            return self::vlessOutbound($scheme, $id);
        }

        if ($scheme instanceof ShadowsocksScheme) {
            return self::shadowsocksOutbound($scheme, $id);
        }

        throw new UnsupportedOutboundTypeException($scheme->getType()->value);
    }

    /**
     * Creates a Vless outbound entity from Vless scheme entity
     *
     * @param VlessScheme $scheme Vless scheme entity
     * @param int $id Outbound id
     *
     * @return VlessOutbound The created Vless outbound entity
     *
     * @throws InvalidArgumentException If required fields are missing or empty
     */
    private static function vlessOutbound(VlessScheme $scheme, int $id): VlessOutbound
    {
        return new VlessOutbound(
            new NonEmptyStringVO($scheme->getTagString()),
            $id,
            new NonEmptyStringVO($scheme->getServer()),
            new PortVO($scheme->getServerPort()),
            new NonEmptyStringVO($scheme->getUuid()),
            $scheme->getFlow() === null ? null : new NonEmptyStringVO($scheme->getFlow()),
            new TLS(
                new NonEmptyStringVO($scheme->getSni()),
                new Reality(
                    new NonEmptyStringVO($scheme->getPbk()),
                    $scheme->getSid() ? new NonEmptyStringVO($scheme->getSid()) : null,
                    true
                ),
                $scheme->getFp() ? new UTLS(
                    new NonEmptyStringVO($scheme->getFp()),
                    true
                ) : null,
                true
            )
        );
    }

    /**
     * Creates a shadowsocks outbound entity from Vless scheme entity
     *
     * @param ShadowsocksScheme $scheme shadowsocks scheme entity
     * @param int $id Outbound id
     *
     * @return ShadowsocksOutbound The created shadowsocks outbound entity
     *
     * @throws InvalidArgumentException If required fields are missing or empty
     */
    private static function shadowsocksOutbound(ShadowsocksScheme $scheme, int $id): ShadowsocksOutbound
    {
        return new ShadowsocksOutbound(
            new NonEmptyStringVO($scheme->getTagString()),
            $id,
            new NonEmptyStringVO($scheme->getServer()),
            new PortVO($scheme->getServerPort()),
            new NonEmptyStringVO($scheme->getMethod()->value),
            new NonEmptyStringVO($scheme->getPassword()),
            $scheme->getPlugin() === null ? null : new NonEmptyStringVO($scheme->getPlugin()->value),
            $scheme->getPluginOptions() === null ? null : new NonEmptyStringVO($scheme->getPluginOptions()),
        );
    }
}