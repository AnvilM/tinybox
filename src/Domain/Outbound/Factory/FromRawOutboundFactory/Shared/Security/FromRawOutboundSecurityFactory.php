<?php

declare(strict_types=1);

namespace App\Domain\Outbound\Factory\FromRawOutboundFactory\Shared\Security;

use App\Domain\Outbound\DTO\RawOutboundDTO;
use App\Domain\Outbound\Exception\UnsupportedSecurityException;
use App\Domain\Outbound\VO\Security\RealitySecurityVO;
use App\Domain\Outbound\VO\Security\SecurityTypeVO;
use App\Domain\Outbound\VO\Security\SecurityVO;
use App\Domain\Shared\VO\Shared\NonEmptyStringVO;
use InvalidArgumentException;

final readonly class FromRawOutboundSecurityFactory
{
    /**
     * @throws UnsupportedSecurityException
     * @throws InvalidArgumentException
     */
    public static function create(RawOutboundDTO $rawOutbound): SecurityVO
    {
        return match (SecurityTypeVO::tryFrom($rawOutbound->security)) {
            SecurityTypeVO::Reality => self::createRealitySecurity($rawOutbound),
            default => throw new UnsupportedSecurityException()
        };
    }


    /**
     * @throws InvalidArgumentException
     */
    private static function createRealitySecurity(RawOutboundDTO $rawOutbound): RealitySecurityVO
    {
        return new RealitySecurityVO(
            new NonEmptyStringVO($rawOutbound->sni),
            new NonEmptyStringVO($rawOutbound->pbk),
            $rawOutbound->sid === null ? null : new NonEmptyStringVO($rawOutbound->sid),
            $rawOutbound->fp === null ? null : new NonEmptyStringVO($rawOutbound->fp),
        );
    }
}