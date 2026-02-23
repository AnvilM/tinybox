<?php

declare(strict_types=1);

namespace App\Core\Collections\SingBoxConfig;

use Ramsey\Collection\Map\TypedMap;

/**
 * @extends TypedMap<string, array>
 */
final class SingBoxConfigMap extends TypedMap
{
    public function __construct(array $data = [])
    {
        parent::__construct('string', 'array', $data);
    }
}