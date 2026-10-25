<?php

declare(strict_types=1);

namespace App\Application\Outbound\Selection\Property;

/**
 * Type of the value a Property yields.
 *
 * NOTE: `Int` is not used by any property yet. It is an intentional extension
 * point (numeric properties such as a port) and part of the Operator contract.
 */
enum ValueKind
{
    case String;

    case Int;
}
