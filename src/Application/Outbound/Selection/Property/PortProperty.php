<?php

declare(strict_types=1);

namespace App\Application\Outbound\Selection\Property;

use App\Domain\Outbound\Entity\Outbound;
use InvalidArgumentException;

/**
 * Server port of the outbound. The first numeric property: supports only `=` and `!=`.
 */
final readonly class PortProperty implements Property
{
    public function name(): string
    {
        return 'port';
    }

    public function kind(): ValueKind
    {
        return ValueKind::Int;
    }

    public function parse(string $raw): int
    {
        $value = trim($raw);

        if (!ctype_digit($value) || strlen($value) > 5 || (int)$value < 1 || (int)$value > 65535) {
            throw new InvalidArgumentException("Port must be an integer from 1 to 65535, got '$raw'");
        }

        return (int)$value;
    }

    public function valueOf(Outbound $outbound): ?int
    {
        return $outbound->getServerPortInt();
    }

    public function allowedValues(): array
    {
        return ['1-65535'];
    }
}
