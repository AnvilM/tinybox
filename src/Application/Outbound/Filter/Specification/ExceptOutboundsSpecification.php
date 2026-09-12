<?php

declare(strict_types=1);

namespace App\Application\Outbound\Filter\Specification;

use App\Domain\Interface\Outbound\OutboundSpecificationInterface;
use App\Domain\Outbound\Entity\Outbound;
use Psl\Collection\VectorInterface;

/**
 * Decorates any specification so that a fixed set of outbound tags always
 * satisfies it, no matter what the wrapped rule says.
 *
 * {@see \App\Application\Outbound\Filter\OutboundFilterService} wraps every
 * specification built from a
 * {@see \App\Application\Outbound\Filter\Contract\OutboundFilterCriteriaExceptOutboundsInterface}
 * with this decorator whenever the criteria carries a non-empty except list.
 * This is the whole mechanism behind `--countryCodeExcept`,
 * `--excludeCountryCodeExcept`, `--outboundTypeExcept` and
 * `--excludeOutboundTypeExcept` - no individual Specification or Factory
 * needs to know about excepting.
 */
final readonly class ExceptOutboundsSpecification implements OutboundSpecificationInterface
{
    /**
     * @param VectorInterface<string> $exceptOutbounds
     */
    public function __construct(
        private OutboundSpecificationInterface $specification,
        private VectorInterface                $exceptOutbounds,
    )
    {
    }

    public function isSatisfiedBy(Outbound $outbound): bool
    {
        foreach ($this->exceptOutbounds as $tag) {
            if ($tag === $outbound->getTagString()) return true;
        }

        return $this->specification->isSatisfiedBy($outbound);
    }
}
