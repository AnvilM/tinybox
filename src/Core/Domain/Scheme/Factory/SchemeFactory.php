<?php

declare(strict_types=1);

namespace App\Core\Domain\Scheme\Factory;

use App\Core\Domain\Scheme\Entity\Scheme;
use App\Core\Domain\Scheme\Exception\UnsupportedSchemeType;
use App\Core\Domain\Scheme\VO\RawSchemeVO;
use InvalidArgumentException;

final readonly class SchemeFactory
{
    /**
     * Creates a Scheme entity from RawSchemeVO value object
     *
     * @param RawSchemeVO $rawSchemeVO RawSchemeVO value object
     *
     * @return Scheme The created Scheme entity
     *
     * @throws InvalidArgumentException If required fields are missing
     * @throws UnsupportedSchemeType If scheme type is unsupported
     */
    public static function fromRawSchemeVO(RawSchemeVO $rawSchemeVO): Scheme
    {
        return new Scheme(
            $rawSchemeVO->type,
            $rawSchemeVO->tag,
            $rawSchemeVO->uuid,
            $rawSchemeVO->server,
            $rawSchemeVO->server_port,
            $rawSchemeVO->sni,
            $rawSchemeVO->pbk,
            $rawSchemeVO->sid,
            $rawSchemeVO->flow,
            $rawSchemeVO->fp
        );
    }
}