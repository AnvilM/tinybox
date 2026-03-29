<?php

declare(strict_types=1);

namespace App\Domain\Outbound\VO;

use App\Domain\Outbound\Exception\UnsupportedOutboundTypeException;
use App\Domain\Scheme\VO\SchemeTypeVO;

enum OutboundTypeVO: string
{
    case Vless = 'vless';

    case Shadowsocks = "shadowsocks";

    /**
     * @throws UnsupportedOutboundTypeException If outbound type is unsupported
     */
    public static function fromSchemeTypeVO(SchemeTypeVO $schemeTypeVO): self
    {
        return match ($schemeTypeVO) {
            SchemeTypeVO::Vless => self::Vless,
            SchemeTypeVO::SS => self::Shadowsocks,
            default => throw new UnsupportedOutboundTypeException($schemeTypeVO->value),
        };
    }
}