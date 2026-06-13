<?php

declare(strict_types=1);

namespace App\Domain\Outbound\VO;

use Psl\Collection\MutableVector;
use Psl\Collection\VectorInterface;

enum OutboundTypeVO: string
{
    case Vless = 'vless';

    case Shadowsocks = "shadowsocks";


    /**
     * Creates outbound types from their string values.
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
            $type = self::tryFrom($stringValue);

            if ($type !== null) {
                $types->add($type);
            }
        }
        
        return $types;
    }
}