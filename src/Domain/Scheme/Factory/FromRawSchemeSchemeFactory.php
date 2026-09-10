<?php

declare(strict_types=1);

namespace App\Domain\Scheme\Factory;

use App\Domain\Scheme\Entity\Scheme;
use App\Domain\Scheme\Exception\UnsupportedSchemeType;
use App\Domain\Scheme\Factory\Shadowsocks\FromRawSchemeShadowsocksSchemeFactory;
use App\Domain\Scheme\Factory\Vless\FromRawSchemeVlessSchemeFactory;
use App\Domain\Scheme\VO\RawSchemeVO;
use App\Domain\Scheme\VO\SchemeTypeVO;
use InvalidArgumentException;

final readonly class FromRawSchemeSchemeFactory
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

            SchemeTypeVO::Vless => FromRawSchemeVlessSchemeFactory::create($rawSchemeVO),
            SchemeTypeVO::SS => FromRawSchemeShadowsocksSchemeFactory::create($rawSchemeVO),
            default => throw new UnsupportedSchemeType($rawSchemeVO->type),
        };
    }

}