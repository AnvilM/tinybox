<?php

declare(strict_types=1);

namespace App\Application\Outbound\Selection\Property;

use App\Domain\Interface\Outbound\SecurityProvider;
use App\Domain\Outbound\Entity\Outbound;
use InvalidArgumentException;

/**
 * TLS fingerprint of the outbound security. Only outbounds with a fingerprint have it.
 */
final readonly class FingerprintProperty implements Property
{
    public function name(): string
    {
        return 'fingerprint';
    }

    public function kind(): ValueKind
    {
        return ValueKind::String;
    }

    public function parse(string $raw): string
    {
        $value = trim($raw);

        if ($value === '') {
            throw new InvalidArgumentException('Fingerprint must not be empty');
        }

        return $value;
    }

    public function valueOf(Outbound $outbound): ?string
    {
        return $outbound instanceof SecurityProvider ? $outbound->getSecurity()?->getFingerprint()?->getValue() : null;
    }

    public function allowedValues(): null
    {
        return null;
    }
}
