<?php

declare(strict_types=1);

namespace App\Application\Outbound\Export\Specification;

use App\Application\Outbound\DTO\Export\CoreType;
use App\Application\Outbound\Export\Interface\OutboundCompatibilitySpecificationInterface;
use App\Domain\Outbound\Entity\Outbound;
use App\Domain\Outbound\Entity\VlessOutbound;
use App\Domain\Outbound\VO\Security\RealitySecurityVO;

/**
 * Both cores decode the Reality credentials at startup and refuse to run
 * with malformed ones, so one broken subscription entry would break the
 * whole config:
 *  - public key: unpadded base64url of exactly 32 bytes;
 *  - short id: hex string of an even length, up to 8 bytes (may be empty/absent).
 */
final class RealityCredentialsValiditySpecification implements OutboundCompatibilitySpecificationInterface
{
    public function isSatisfiedBy(Outbound $outbound, CoreType $core): bool
    {
        if (!$outbound instanceof VlessOutbound) return true;

        $security = $outbound->getSecurity();

        if (!$security instanceof RealitySecurityVO) return true;

        return $this->isValidPublicKey($security->getPublicKey()->getValue()) &&
            $this->isValidShortId($security->getShortId()?->getValue());
    }

    public function reason(): string
    {
        return 'Reality public key must be a base64url encoded 32 bytes key and short id must be a hex string of up to 16 characters (even length)';
    }

    private function isValidPublicKey(string $publicKey): bool
    {
        if (preg_match('/^[A-Za-z0-9_-]+$/', $publicKey) !== 1) return false;

        $decoded = base64_decode(strtr($publicKey, '-_', '+/'), true);

        return $decoded !== false && strlen($decoded) === 32;
    }

    private function isValidShortId(?string $shortId): bool
    {
        if ($shortId === null || $shortId === '') return true;

        return strlen($shortId) <= 16 && strlen($shortId) % 2 === 0 && ctype_xdigit($shortId);
    }
}
