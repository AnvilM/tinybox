<?php

declare(strict_types=1);

namespace App\Domain\Scheme\Factory;

use App\Domain\Scheme\Entity\Scheme;
use App\Domain\Scheme\VO\RawSchemeVO;
use App\Domain\Shared\VO\Outbound\OutboundTypeVO;
use App\Domain\Shared\VO\Outbound\Transport\TransportTypeVO;
use App\Domain\Shared\VO\Shared\NonEmptyStringVO;
use App\Domain\Shared\VO\Shared\PortVO;
use InvalidArgumentException;
use ValueError;

final readonly class SchemeFactory
{
    /**
     * Creates a Scheme entity from RawSchemeVO value object
     *
     * @param RawSchemeVO $rawSchemeVO RawSchemeVO value object
     *
     * @return Scheme Created Scheme entity
     *
     * @throws InvalidArgumentException If required fields are missing
     */
    public static function fromRawSchemeVO(RawSchemeVO $rawSchemeVO): Scheme
    {
        try {
            return new Scheme(
                OutboundTypeVO::from($rawSchemeVO->type),
                new NonEmptyStringVO($rawSchemeVO->uuid),
                new NonEmptyStringVO($rawSchemeVO->server),
                new PortVO($rawSchemeVO->server_port),
                new NonEmptyStringVO($rawSchemeVO->sni),
                new NonEmptyStringVO($rawSchemeVO->pbk),
                new NonEmptyStringVO($rawSchemeVO->sid),
                $rawSchemeVO->tag === null ? null : new NonEmptyStringVO($rawSchemeVO->tag),
                $rawSchemeVO->flow === null ? null : new NonEmptyStringVO($rawSchemeVO->flow),
                $rawSchemeVO->fp === null ? null : new NonEmptyStringVO($rawSchemeVO->fp),
                $rawSchemeVO->transportType === null || $rawSchemeVO->transportType === 'tcp' ? null : TransportTypeVO::from($rawSchemeVO->transportType),
            );
        } catch (ValueError) {
            throw new InvalidArgumentException();
        }

    }
}