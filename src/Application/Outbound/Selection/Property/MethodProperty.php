<?php

declare(strict_types=1);

namespace App\Application\Outbound\Selection\Property;

use App\Domain\Outbound\Entity\Outbound;
use App\Domain\Outbound\Entity\ShadowsocksOutbound;
use App\Domain\Outbound\VO\Shadowsocks\Userinfo\ShadowsocksMethod;
use InvalidArgumentException;

/**
 * Encryption method of a shadowsocks outbound.
 */
final readonly class MethodProperty implements Property
{
    public function name(): string
    {
        return 'method';
    }

    public function kind(): ValueKind
    {
        return ValueKind::String;
    }

    public function parse(string $raw): string
    {
        $value = strtolower(trim($raw));

        if (ShadowsocksMethod::tryFrom($value) === null) {
            throw new InvalidArgumentException("Unknown method '$raw'");
        }

        return $value;
    }

    public function valueOf(Outbound $outbound): ?string
    {
        return $outbound instanceof ShadowsocksOutbound ? $outbound->getUserinfo()->getMethod()->value : null;
    }

    public function allowedValues(): array
    {
        return array_map(static fn(ShadowsocksMethod $case): string => $case->value, ShadowsocksMethod::cases());
    }
}
