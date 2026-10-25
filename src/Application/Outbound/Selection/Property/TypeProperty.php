<?php

declare(strict_types=1);

namespace App\Application\Outbound\Selection\Property;

use App\Domain\Outbound\Entity\Outbound;
use App\Domain\Outbound\VO\ProtocolVO;
use InvalidArgumentException;

final readonly class TypeProperty implements Property
{
    /**
     * Aliases accepted by ProtocolVO::tryFromAlias(), only for error messages.
     */
    private const array ALIASES = [
        'shadowsocks' => 'ss',
    ];

    public function name(): string
    {
        return 'type';
    }

    public function kind(): ValueKind
    {
        return ValueKind::String;
    }

    public function parse(string $raw): string
    {
        $protocol = ProtocolVO::tryFromAlias(strtolower(trim($raw)));

        if ($protocol === null) {
            throw new InvalidArgumentException("Unknown outbound type '$raw'");
        }

        return $protocol->value;
    }

    public function valueOf(Outbound $outbound): string
    {
        return $outbound->getType()->value;
    }

    public function allowedValues(): array
    {
        $values = [];

        foreach (ProtocolVO::cases() as $protocol) {
            $alias = self::ALIASES[$protocol->value] ?? null;

            $values[] = $alias === null ? $protocol->value : "$protocol->value (alias: $alias)";
        }

        return $values;
    }
}
