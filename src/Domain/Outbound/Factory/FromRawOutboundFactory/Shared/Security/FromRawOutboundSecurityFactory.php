<?php

declare(strict_types=1);

namespace App\Domain\Outbound\Factory\FromRawOutboundFactory\Shared\Security;

use App\Domain\Outbound\Exception\UnsupportedSecurityException;
use App\Domain\Outbound\VO\RawOutboundVO;
use App\Domain\Outbound\VO\Security\RealitySecurityVO;
use App\Domain\Outbound\VO\Security\SecurityTypeVO;
use App\Domain\Outbound\VO\Security\SecurityVO;
use App\Domain\Outbound\VO\Security\TLSSecurityVO;
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
            SecurityTypeVO::TLS => $this->createTLSSecurity($rawOutbound),
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


    /**
     * @throws InvalidArgumentException
     */
    private function createTLSSecurity(RawOutboundVO $rawOutbound): TLSSecurityVO
    {
        return new TLSSecurityVO(
            new NonEmptyStringVO($rawOutbound->sni),
            $rawOutbound->fp === null ? null : new NonEmptyStringVO($rawOutbound->fp),
            $this->normalizeAlpn($rawOutbound->alpn),
        );
    }

    /**
     * Keeps only valid ALPN protocol names (comma separated list, e.g. "h2,http/1.1")
     *
     * Subscriptions sometimes contain broken values (e.g. a multiple times percent-encoded "http%252F1.1")
     * that no server understands: they are ignored instead of producing a distinct outbound for each variant.
     *
     * @throws InvalidArgumentException
     */
    private function normalizeAlpn(?string $alpn): ?NonEmptyStringVO
    {
        if ($alpn === null) return null;

        $protocols = array_filter(
            array_map('trim', explode(',', $alpn)),
            static fn(string $protocol): bool => preg_match('~^[A-Za-z0-9][A-Za-z0-9._/-]*$~', $protocol) === 1,
        );

        return $protocols === [] ? null : new NonEmptyStringVO(implode(',', array_unique($protocols)));
    }
}