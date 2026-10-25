<?php

declare(strict_types=1);

namespace App\Application\Outbound\Selection\Property;

use App\Domain\Outbound\Entity\Outbound;
use App\Domain\Outbound\Entity\VlessOutbound;
use InvalidArgumentException;

/**
 * Flow of a vless outbound. Outbounds without a flow have no value.
 */
final readonly class FlowProperty implements Property
{
    public function name(): string
    {
        return 'flow';
    }

    public function kind(): ValueKind
    {
        return ValueKind::String;
    }

    public function parse(string $raw): string
    {
        $value = trim($raw);

        if ($value === '') {
            throw new InvalidArgumentException('Flow must not be empty');
        }

        return $value;
    }

    public function valueOf(Outbound $outbound): ?string
    {
        return $outbound instanceof VlessOutbound ? $outbound->getFlowString() : null;
    }

    public function allowedValues(): null
    {
        return null;
    }
}
