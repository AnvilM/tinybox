<?php

declare(strict_types=1);

namespace App\Domain\Outbound\Factory\FromRawOutboundFactory\Shared\Security;

use App\Domain\Outbound\Exception\UnsupportedSecurityException;
use App\Domain\Outbound\VO\RawOutboundVO;
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
    public function create(RawOutboundVO $rawOutbound): SecurityVO
    {
        return match (SecurityTypeVO::tryFrom($rawOutbound->security)) {
            SecurityTypeVO::Reality => $this->createRealitySecurity($rawOutbound),
            default => throw new UnsupportedSecurityException()
        };
    }


    /**
     * @throws InvalidArgumentException
     */
    private function createRealitySecurity(RawOutboundVO $rawOutbound): RealitySecurityVO
    {
        return new RealitySecurityVO(
            new NonEmptyStringVO($rawOutbound->sni),
            new NonEmptyStringVO($rawOutbound->pbk),
            $rawOutbound->sid === null ? null : new NonEmptyStringVO($rawOutbound->sid),
            $rawOutbound->fp === null ? null : new NonEmptyStringVO($rawOutbound->fp),
            $rawOutbound->spx === null ? null : new NonEmptyStringVO($rawOutbound->spx),
        );
    }
}