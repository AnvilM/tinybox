<?php

declare(strict_types=1);

namespace App\Infrastructure\Outbound\Parser;

use App\Domain\Outbound\Exception\UnsupportedOutboundTypeException;
use App\Domain\Outbound\VO\OutboundTypeVO;
use App\Domain\Outbound\VO\RawOutbound\RawOutboundVO;
use App\Domain\Shared\Ports\Outbound\Parser\RawOutboundParserPort;
use App\Infrastructure\Outbound\Parser\Parser\RawShadowsocksOutboundParser;
use App\Infrastructure\Outbound\Parser\Parser\RawVlessOutboundParser;
use ValueError;

final readonly class RawOutboundParser implements RawOutboundParserPort
{
    /**
     * @inheritdoc
     */
    public function parse(array $rawOutbound): RawOutboundVO
    {
        try {
            $outboundType = OutboundTypeVO::from($rawOutbound['type'] ?? "");
        } catch (ValueError) {
            throw new UnsupportedOutboundTypeException("Outbound type {$rawOutbound['type']} not supported");
        }

        return match ($outboundType) {
            OutboundTypeVO::Vless => RawVlessOutboundParser::handle($rawOutbound),
            OutboundTypeVO::Shadowsocks => RawShadowsocksOutboundParser::handle($rawOutbound),
            default => throw new UnsupportedOutboundTypeException("Unsupported Outbound Type"),
        };
    }
}