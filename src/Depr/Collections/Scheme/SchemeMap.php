<?php

declare(strict_types=1);

namespace App\Core\Collections\Scheme;

use App\Core\Collections\Scheme\Collection\SchemeCollection;
use Ramsey\Collection\Map\TypedMap;

/**
 * @extends TypedMap<string, SchemeCollection>
 */
final class SchemeMap extends TypedMap
{
    public function __construct(array $data = [])
    {
        parent::__construct('string', SchemeCollection::class, $data);
    }
}