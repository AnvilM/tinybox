<?php

declare(strict_types=1);

namespace App\Domain\Scheme\Factory\Shadowsocks;

use App\Domain\Scheme\Entity\ShadowsocksScheme;
use App\Domain\Scheme\VO\RawSchemeVO;
use App\Domain\Shared\VO\Outbound\Shadowsocks\Plugin\ShadowsocksPluginVO;
use App\Domain\Shared\VO\Outbound\Shadowsocks\Userinfo\ShadowsocksUserinfoVO;
use App\Domain\Shared\VO\Outbound\Transport\TransportTypeVO;
use App\Domain\Shared\VO\Shared\NonEmptyStringVO;
use App\Domain\Shared\VO\Shared\PortVO;
use InvalidArgumentException;
use ValueError;

final readonly class FromRawSchemeShadowsocksSchemeFactory
{
    /**
     * Creates a shadowsocks scheme entity from RawSchemeVO value object
     *
     * @param RawSchemeVO $rawSchemeVO RawSchemeVO value object
     *
     * @return ShadowsocksScheme Created shadowsocks scheme entity
     *
     * @throws InvalidArgumentException If required fields are missing or provided invalid fields
     */
    public static function create(RawSchemeVO $rawSchemeVO): ShadowsocksScheme
    {
        try {
            return new ShadowsocksScheme(
                $rawSchemeVO->tag === null ? null : new NonEmptyStringVO($rawSchemeVO->tag),
                new ShadowsocksUserinfoVO($rawSchemeVO->uuid),
                $rawSchemeVO->shadowsocksPlugin === null ? null : new ShadowsocksPluginVO($rawSchemeVO->shadowsocksPlugin),
                new NonEmptyStringVO($rawSchemeVO->server),
                new PortVO($rawSchemeVO->server_port),
                $rawSchemeVO->transportType === null || $rawSchemeVO->transportType === 'tcp' ? null : TransportTypeVO::from($rawSchemeVO->transportType),
                new NonEmptyStringVO($rawSchemeVO->path),
                new NonEmptyStringVO($rawSchemeVO->host)
            );
        } catch (ValueError) {
            throw new InvalidArgumentException();
        }

    }
}