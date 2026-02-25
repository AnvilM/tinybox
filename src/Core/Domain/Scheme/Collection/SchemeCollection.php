<?php

declare(strict_types=1);

namespace App\Core\Domain\Scheme\Collection;

use App\Core\Domain\Scheme\Entity\Scheme;
use App\Core\Shared\Collection\AbstractCollection;

/**
 * @extends AbstractCollection<Scheme>
 */
final class SchemeCollection extends AbstractCollection
{
    public function getType(): string
    {
        return Scheme::class;
    }
}