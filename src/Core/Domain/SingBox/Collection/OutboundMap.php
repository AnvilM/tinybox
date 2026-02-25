<?php

declare(strict_types=1);

namespace App\Core\Domain\SingBox\Collection;

use Ramsey\Collection\Map\TypedMap;

/**
 * @extends TypedMap<string, OutboundCollection>
 */
final class OutboundMap extends TypedMap
{
    public function __construct(array $data = [])
    {
        parent::__construct('string', OutboundCollection::class, $data);
    }
}