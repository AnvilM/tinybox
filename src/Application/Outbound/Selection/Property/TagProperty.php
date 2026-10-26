<?php

declare(strict_types=1);

namespace App\Application\Outbound\Selection\Property;

use App\Domain\Outbound\Entity\Outbound;
use InvalidArgumentException;

final readonly class TagProperty implements Property
{
    public function name(): string
    {
        return 'tag';
    }

    public function kind(): ValueKind
    {
        return ValueKind::String;
    }

    public function parse(string $raw): string
    {
        if ($raw === '') {
            throw new InvalidArgumentException('Tag must not be empty');
        }

        return $raw;
    }

    public function valueOf(Outbound $outbound): string
    {
        return $outbound->getTagString();
    }

    public function allowedValues(): null
    {
        return null;
    }
}
