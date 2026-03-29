<?php

declare(strict_types=1);

namespace App\Domain\Scheme\VO;

use App\Domain\Scheme\Exception\UnsupportedSchemeType;

enum SchemeTypeVO: string
{
    case Vless = 'vless';

    case SS = "ss";

    /**
     * @throws UnsupportedSchemeType If scheme type is unsupported
     */
    public static function fromString(string $type): self
    {
        return self::tryFrom($type) ?? throw new UnsupportedSchemeType($type);
    }
}
