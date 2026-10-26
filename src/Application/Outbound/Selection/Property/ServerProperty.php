<?php

declare(strict_types=1);

namespace App\Application\Outbound\Selection\Property;

use App\Domain\Outbound\Entity\Outbound;
use InvalidArgumentException;

/**
 * Server host (domain or IP) of the outbound.
 */
final readonly class ServerProperty implements Property
{
    public function name(): string
    {
        return 'server';
    }

    public function kind(): ValueKind
    {
        return ValueKind::String;
    }

    public function parse(string $raw): string
    {
        $value = trim($raw);

        if ($value === '') {
            throw new InvalidArgumentException('Server must not be empty');
        }

        return $value;
    }

    public function valueOf(Outbound $outbound): ?string
    {
        return $outbound->getServerString();
    }

    public function allowedValues(): null
    {
        return null;
    }
}
