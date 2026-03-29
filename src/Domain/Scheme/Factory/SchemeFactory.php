<?php

declare(strict_types=1);

namespace App\Domain\Scheme\Factory;

use App\Domain\Scheme\Entity\Scheme;
use App\Domain\Scheme\Entity\ShadowsocksScheme;
use App\Domain\Scheme\Entity\VlessScheme;
use App\Domain\Scheme\Exception\UnsupportedSchemeType;
use App\Domain\Scheme\VO\RawSchemeVO;
use App\Domain\Scheme\VO\SchemeTypeVO;
use App\Domain\Scheme\VO\ShadowsocksScheme\Plugin\ShadowsocksPluginVO;
use App\Domain\Scheme\VO\ShadowsocksScheme\Userinfo\ShadowsocksUserinfoVO;
use App\Domain\Shared\VO\Outbound\Transport\TransportTypeVO;
use App\Domain\Shared\VO\Shared\NonEmptyStringVO;
use App\Domain\Shared\VO\Shared\PortVO;
use InvalidArgumentException;
use ValueError;

final readonly class SchemeFactory
{
    /**
     * Creates a Scheme entity from RawSchemeVO value object
     *
     * @param RawSchemeVO $rawSchemeVO RawSchemeVO value object
     *
     * @return Scheme Created Scheme entity
     *
     * @throws InvalidArgumentException If required fields are missing
     * @throws UnsupportedSchemeType If scheme type is unsupported
     */
    public static function fromRawSchemeVO(RawSchemeVO $rawSchemeVO): Scheme
    {
        return match (SchemeTypeVO::fromString($rawSchemeVO->type)) {
            SchemeTypeVO::Vless => self::vlessScheme($rawSchemeVO),
            SchemeTypeVO::SS => self::shadowsocksScheme($rawSchemeVO),
            default => throw new UnsupportedSchemeType($rawSchemeVO->type),
        };
    }


    /**
     * Creates a Vless scheme entity from RawSchemeVO value object
     *
     * @param RawSchemeVO $rawSchemeVO RawSchemeVO value object
     *
     * @return VlessScheme Created vless scheme entity
     *
     * @throws InvalidArgumentException If required fields are missing or provided invalid fields
     */
    private static function vlessScheme(RawSchemeVO $rawSchemeVO): VlessScheme
    {
        try {
            return new VlessScheme(
                new NonEmptyStringVO($rawSchemeVO->uuid),
                new NonEmptyStringVO($rawSchemeVO->server),
                new PortVO($rawSchemeVO->server_port),
                new NonEmptyStringVO($rawSchemeVO->sni),
                new NonEmptyStringVO($rawSchemeVO->pbk),
                new NonEmptyStringVO($rawSchemeVO->sid),
                $rawSchemeVO->tag === null ? null : new NonEmptyStringVO($rawSchemeVO->tag),
                $rawSchemeVO->flow === null ? null : new NonEmptyStringVO($rawSchemeVO->flow),
                $rawSchemeVO->fp === null ? null : new NonEmptyStringVO($rawSchemeVO->fp),
                $rawSchemeVO->transportType === null || $rawSchemeVO->transportType === 'tcp' ? null : TransportTypeVO::from($rawSchemeVO->transportType),
            );
        } catch (ValueError) {
            throw new InvalidArgumentException();
        }
    }


    /**
     * Creates a shadowsocks scheme entity from RawSchemeVO value object
     *
     * @param RawSchemeVO $rawSchemeVO RawSchemeVO value object
     *
     * @return ShadowsocksScheme Created shadowsocks scheme entity
     *
     * @throws InvalidArgumentException If required fields are missing or provided invalid fields
     */
    private static function shadowsocksScheme(RawSchemeVO $rawSchemeVO): ShadowsocksScheme
    {
        return new ShadowsocksScheme(
            $rawSchemeVO->tag === null ? null : new NonEmptyStringVO($rawSchemeVO->tag),
            new ShadowsocksUserinfoVO($rawSchemeVO->uuid),
            $rawSchemeVO->shadowsocksPlugin === null ? null : new ShadowsocksPluginVO($rawSchemeVO->shadowsocksPlugin),
            new NonEmptyStringVO($rawSchemeVO->server),
            new PortVO($rawSchemeVO->server_port),
        );
    }
}