<?php

declare(strict_types=1);

namespace App\Domain\Scheme\Factory\Vless;

use App\Domain\Scheme\Entity\VlessScheme;
use App\Domain\Scheme\VO\RawSchemeVO;
use App\Domain\Scheme\VO\SchemeSecurityVO;
use App\Domain\Shared\VO\Outbound\Transport\TransportTypeVO;
use App\Domain\Shared\VO\Shared\NonEmptyStringVO;
use App\Domain\Shared\VO\Shared\PortVO;
use InvalidArgumentException;
use ValueError;

final readonly class FromRawSchemeVlessSchemeFactory
{
    /**
     * Creates a Vless scheme entity from RawSchemeVO value object
     *
     * @param RawSchemeVO $rawSchemeVO RawSchemeVO value object
     *
     * @return VlessScheme Created vless scheme entity
     *
     * @throws InvalidArgumentException If required fields are missing or provided invalid fields
     */
    public static function create(RawSchemeVO $rawSchemeVO): VlessScheme
    {
        try {
            return new VlessScheme(
                new NonEmptyStringVO($rawSchemeVO->uuid),
                new NonEmptyStringVO($rawSchemeVO->server),
                new PortVO($rawSchemeVO->server_port),
                new NonEmptyStringVO($rawSchemeVO->sni),
                new NonEmptyStringVO($rawSchemeVO->pbk),
                $rawSchemeVO->sid === null ? null : new NonEmptyStringVO($rawSchemeVO->sid),
                $rawSchemeVO->tag === null ? null : new NonEmptyStringVO($rawSchemeVO->tag),
                $rawSchemeVO->flow === null ? null : new NonEmptyStringVO($rawSchemeVO->flow),
                $rawSchemeVO->fp === null ? null : new NonEmptyStringVO($rawSchemeVO->fp),
                $rawSchemeVO->transportType === null || $rawSchemeVO->transportType === 'tcp' ? null : TransportTypeVO::from($rawSchemeVO->transportType),
                $rawSchemeVO->security === null ? null : SchemeSecurityVO::from($rawSchemeVO->security),
                new NonEmptyStringVO($rawSchemeVO->path),
                new NonEmptyStringVO($rawSchemeVO->host)
            );
        } catch (ValueError) {
            throw new InvalidArgumentException();
        }
    }
}