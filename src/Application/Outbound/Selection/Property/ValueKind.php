<?php

declare(strict_types=1);

namespace App\Application\Outbound\Selection\Property;

/**
 * Type of the value a Property yields.
 *
 * NOTE: `Int` is used by numeric properties (`port`). Such properties support
 * only `=` and `!=`; the glob operator accepts `String` only.
 */
enum ValueKind
{
    case String;

    case Int;
}
