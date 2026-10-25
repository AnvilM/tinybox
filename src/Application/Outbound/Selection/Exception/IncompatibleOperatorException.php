<?php

declare(strict_types=1);

namespace App\Application\Outbound\Selection\Exception;

/**
 * Thrown when an operator cannot be applied to the kind of the property value.
 */
final class IncompatibleOperatorException extends SelectionException
{
    public function __construct(string $operator, string $property, string $source, int $position)
    {
        parent::__construct("Operator '$operator' is not supported for property '$property'", $source, $position);
    }
}
