<?php

declare(strict_types=1);

namespace App\Application\Outbound\Selection\Operator;

use App\Application\Outbound\Selection\Property\ValueKind;
use Closure;

/**
 * Comparison between a property value and the operands of a condition.
 */
interface Operator
{
    /**
     * Symbol used in expressions: '=', '~'.
     */
    public function symbol(): string;

    public function supports(ValueKind $kind): bool;

    /**
     * true - operands are property values and go through Property::parse(),
     * false - operands are patterns and are used as typed.
     */
    public function operandsAreValues(): bool;

    /**
     * @param list<string|int> $operands
     *
     * @return Closure(string|int): bool Called only for non-null property values
     */
    public function matcher(array $operands): Closure;
}
