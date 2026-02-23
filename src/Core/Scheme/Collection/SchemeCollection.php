<?php

declare(strict_types=1);

namespace App\Core\Scheme\Collection;

use App\Core\Scheme\Entity\Scheme;
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