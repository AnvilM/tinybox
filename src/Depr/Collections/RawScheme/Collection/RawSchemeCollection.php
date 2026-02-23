<?php

declare(strict_types=1);

namespace App\Core\Collections\RawScheme\Collection;

use App\Core\Entity\RawScheme;
use Ramsey\Collection\AbstractCollection;

/**
 * @extends AbstractCollection<RawScheme>
 */
final class RawSchemeCollection extends AbstractCollection
{
    public function getType(): string
    {
        return RawScheme::class;
    }
}