<?php

declare(strict_types=1);

namespace App\Domain\Outbound\VO;

use Psl\Collection\MutableVector;
use Psl\Collection\VectorInterface;

enum ProtocolVO: string
{
    case Vless = 'vless';

    case Shadowsocks = "shadowsocks";


    /**
     * Creates outbound protocols from their string values.
     *
     * NOTE: Invalid values are ignored.
     *
     * @param VectorInterface<string> $stringValues String values
     *
     * @return MutableVector<self>
     */
    public static function fromStringValues(VectorInterface $stringValues): MutableVector
    {
        $types = new MutableVector([]);

        foreach ($stringValues as $stringValue) {
            $type = self::tryFromAlias($stringValue);

            if ($type !== null) {
                $types->add($type);
            }
        }

        return $types;
    }

    public static function tryFromAlias(int|string $value): ?ProtocolVO
    {
        return ProtocolVO::tryFrom($value)
            ?? match ($value) {
                'ss' => ProtocolVO::Shadowsocks,
                default => null,
            };
    }
}