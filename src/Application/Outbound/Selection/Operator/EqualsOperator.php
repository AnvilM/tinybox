<?php

declare(strict_types=1);

namespace App\Application\Outbound\Selection\Operator;

use App\Application\Outbound\Selection\Property\ValueKind;
use Closure;

/**
 * `prop = a,b` - the value equals any of the operands.
 */
final readonly class EqualsOperator implements Operator
{
    public function symbol(): string
    {
        return '=';
    }

    public function supports(ValueKind $kind): bool
    {
        return true;
    }

    public function operandsAreValues(): bool
    {
        return true;
    }

    public function matcher(array $operands): Closure
    {
        return static fn(string|int $value): bool => in_array($value, $operands, true);
    }
}
