<?php

declare(strict_types=1);

namespace App\Application\Outbound\Selection\Property;

use App\Domain\Outbound\Entity\Outbound;
use App\Domain\Interface\Outbound\SecurityProvider;
use App\Domain\Outbound\VO\Security\SecurityTypeVO;
use InvalidArgumentException;

/**
 * Security type of the outbound (reality, tls). Outbounds without security have no value.
 */
final readonly class SecurityProperty implements Property
{
    public function name(): string
    {
        return 'security';
    }

    public function kind(): ValueKind
    {
        return ValueKind::String;
    }

    public function parse(string $raw): string
    {
        $value = strtolower(trim($raw));

        if (SecurityTypeVO::tryFrom($value) === null) {
            throw new InvalidArgumentException("Unknown security '$raw'");
        }

        return $value;
    }

    public function valueOf(Outbound $outbound): ?string
    {
        return $outbound instanceof SecurityProvider ? $outbound->getSecurity()?->getType()->value : null;
    }

    public function allowedValues(): array
    {
        return array_map(static fn(SecurityTypeVO $case): string => $case->value, SecurityTypeVO::cases());
    }
}
