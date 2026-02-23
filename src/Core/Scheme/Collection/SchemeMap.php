<?php

declare(strict_types=1);

namespace App\Core\Scheme\Collection;

use Ramsey\Collection\Map\TypedMap;

/**
 * Map of name => SchemeCollection
 *
 * @extends TypedMap<string, SchemeCollection>
 */
final class SchemeMap extends TypedMap
{
    public function __construct(array $data = [])
    {
        parent::__construct('string', SchemeCollection::class, $data);
    }
}