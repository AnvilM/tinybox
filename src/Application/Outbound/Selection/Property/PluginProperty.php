<?php

declare(strict_types=1);

namespace App\Application\Outbound\Selection\Property;

use App\Domain\Outbound\Entity\Outbound;
use App\Domain\Outbound\Entity\ShadowsocksOutbound;
use App\Domain\Outbound\VO\Shadowsocks\Plugin\ShadowsocksPlugin;
use InvalidArgumentException;

/**
 * Plugin of a shadowsocks outbound. Outbounds without a plugin have no value.
 */
final readonly class PluginProperty implements Property
{
    public function name(): string
    {
        return 'plugin';
    }

    public function kind(): ValueKind
    {
        return ValueKind::String;
    }

    public function parse(string $raw): string
    {
        $value = strtolower(trim($raw));

        if (ShadowsocksPlugin::tryFrom($value) === null) {
            throw new InvalidArgumentException("Unknown plugin '$raw'");
        }

        return $value;
    }

    public function valueOf(Outbound $outbound): ?string
    {
        return $outbound instanceof ShadowsocksOutbound ? $outbound->getPlugin()?->getPlugin()->value : null;
    }

    public function allowedValues(): array
    {
        return array_map(static fn(ShadowsocksPlugin $case): string => $case->value, ShadowsocksPlugin::cases());
    }
}
