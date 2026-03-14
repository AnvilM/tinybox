<?php

declare(strict_types=1);

namespace App\Domain\Scheme\Collection;

use App\Domain\Scheme\Entity\Scheme;
use Ramsey\Collection\AbstractCollection;

/**
 * @extends AbstractCollection<Scheme>
 */
final class SchemeCollection extends AbstractCollection
{
    public function getType(): string
    {
        return Scheme::class;
    }

    public function toJson(): string
    {
        return json_encode($this->map(
            fn($scheme) => $scheme->toRawScheme())->toArray(),
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT
        );
    }


    public function has(Scheme $scheme): bool
    {
        foreach ($this->toArray() as $schemeItem) {

            if ($schemeItem->equals($scheme)) return true;
        }

        return false;
    }
}