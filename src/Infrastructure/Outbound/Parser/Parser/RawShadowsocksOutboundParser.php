<?php

declare(strict_types=1);

namespace App\Infrastructure\Outbound\Parser\Parser;

use App\Domain\Outbound\VO\RawOutbound\RawShadowsocksOutboundVO;
use InvalidArgumentException;
use Throwable;

final readonly class RawShadowsocksOutboundParser
{
    /**
     * Parse shadowsocks outbound as JSON decoded array to raw shadowsocks outbound value object
     *
     * @param array $jsonDecodedShadowsocksOutbound Shadowsocks outbound as JSON decoded array
     *
     * @return RawShadowsocksOutboundVO Raw shadowsocks outbound value object
     *
     * @throws InvalidArgumentException
     */
    public static function handle(array $jsonDecodedShadowsocksOutbound): RawShadowsocksOutboundVO
    {
        try {
            return new RawShadowsocksOutboundVO(
                $jsonDecodedShadowsocksOutbound['type'],
                $jsonDecodedShadowsocksOutbound['tag'],
                $jsonDecodedShadowsocksOutbound['server'],
                $jsonDecodedShadowsocksOutbound['server_port'],
                $jsonDecodedShadowsocksOutbound['method'],
                $jsonDecodedShadowsocksOutbound['password'],
                $jsonDecodedShadowsocksOutbound['plugin'] ?? null,
                $jsonDecodedShadowsocksOutbound['plugin_opts'] ?? null
            );
        } catch (Throwable) {
            throw new InvalidArgumentException();
        }
    }
}