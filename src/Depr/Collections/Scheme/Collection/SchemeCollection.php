<?php

declare(strict_types=1);

namespace App\Core\Collections\Scheme\Collection;

use App\Core\Entity\Scheme;
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
}