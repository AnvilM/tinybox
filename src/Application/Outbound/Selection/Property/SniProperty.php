<?php

declare(strict_types=1);

namespace App\Application\Outbound\Selection\Property;

use App\Domain\Interface\Outbound\SecurityProvider;
use App\Domain\Outbound\Entity\Outbound;
use InvalidArgumentException;

/**
 * Server name (SNI) of the outbound security. Only outbounds with security have it.
 */
final readonly class SniProperty implements Property
{
    public function name(): string
    {
        return 'sni';
    }

    public function kind(): ValueKind
    {
        return ValueKind::String;
    }

    public function parse(string $raw): string
    {
        $value = trim($raw);

        if ($value === '') {
            throw new InvalidArgumentException('Server name must not be empty');
        }

        return $value;
    }

    public function valueOf(Outbound $outbound): ?string
    {
        return $outbound instanceof SecurityProvider ? $outbound->getSecurity()?->getServerName()->getValue() : null;
    }

    public function allowedValues(): null
    {
        return null;
    }
}
