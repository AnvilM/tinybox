<?php

declare(strict_types=1);

namespace App\Domain\Outbound\VO;

enum ProtocolVO: string
{
    case Vless = 'vless';

    case Shadowsocks = "shadowsocks";


    public static function tryFromAlias(int|string $value): ?ProtocolVO
    {
        return ProtocolVO::tryFrom($value)
            ?? match ($value) {
                'ss' => ProtocolVO::Shadowsocks,
                default => null,
            };
    }
}