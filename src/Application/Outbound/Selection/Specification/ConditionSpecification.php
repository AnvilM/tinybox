<?php

declare(strict_types=1);

namespace App\Application\Outbound\Selection\Specification;

use App\Application\Outbound\Selection\Property\Property;
use App\Domain\Interface\Outbound\OutboundSpecificationInterface;
use App\Domain\Outbound\Entity\Outbound;
use Closure;

/**
 * An outbound without a value for the property never satisfies the condition.
 */
final readonly class ConditionSpecification implements OutboundSpecificationInterface
{
    /**
     * @param Closure(string|int): bool $matcher
     */
    public function __construct(
        private Property $property,
        private Closure  $matcher,
    )
    {
    }

    public function isSatisfiedBy(Outbound $outbound): bool
    {
        $value = $this->property->valueOf($outbound);

        if ($value === null) {
            return false;
        }

        return ($this->matcher)($value);
    }
}
