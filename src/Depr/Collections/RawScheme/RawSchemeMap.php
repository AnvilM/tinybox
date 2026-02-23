<?php

declare(strict_types=1);

namespace App\Core\Collections\RawScheme;

use App\Core\Collections\RawScheme\Collection\RawSchemeCollection;
use Ramsey\Collection\Map\TypedMap;

/**
 * @extends TypedMap<string, RawSchemeCollection>
 *
 */
final class RawSchemeMap extends TypedMap
{
    public function __construct(array $data = [])
    {
        parent::__construct('string', RawSchemeCollection::class, $data);
    }
}