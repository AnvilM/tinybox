<?php

declare(strict_types=1);

namespace App\Application\Outbound\Selection\Property;

use App\Domain\Outbound\Entity\Outbound;
use App\Domain\Outbound\Entity\VlessOutbound;
use App\Domain\Outbound\VO\Transport\TransportTypeVO;
use InvalidArgumentException;

/**
 * Transport type of the outbound (ws, xhttp, ...). Outbounds without transport have no value.
 */
final readonly class TransportProperty implements Property
{
    public function name(): string
    {
        return 'transport';
    }

    public function kind(): ValueKind
    {
        return ValueKind::String;
    }

    public function parse(string $raw): string
    {
        $value = strtolower(trim($raw));

        if (TransportTypeVO::tryFrom($value) === null) {
            throw new InvalidArgumentException("Unknown transport '$raw'");
        }

        return $value;
    }

    public function valueOf(Outbound $outbound): ?string
    {
        return $outbound instanceof VlessOutbound ? $outbound->getTransport()?->getType()->value : null;
    }

    public function allowedValues(): array
    {
        return array_map(static fn(TransportTypeVO $case): string => $case->value, TransportTypeVO::cases());
    }
}
