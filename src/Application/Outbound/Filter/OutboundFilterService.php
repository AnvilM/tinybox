<?php

declare(strict_types=1);

namespace App\Application\Outbound\Filter;

use App\Application\Outbound\Filter\Contract\OutboundFilterCriteriaExceptOutboundsInterface;
use App\Application\Outbound\Filter\Contract\OutboundFilterCriteriaInterface;
use App\Application\Outbound\Filter\Specification\ExceptOutboundsSpecification;
use App\Domain\Interface\Outbound\OutboundSpecificationInterface;
use App\Domain\Outbound\Collection\OutboundMap;
use App\Domain\Shared\Exception\CriticalException;
use Psl\Collection\MutableVector;
use Psl\Collection\VectorInterface;

/**
 * Turns a flat list of filter criteria into a filtered {@see OutboundMap}.
 *
 * This is the single entry point use cases should depend on - they never
 * need to know about specifications or factories.
 */
final readonly class OutboundFilterService
{
    public function __construct(
        private OutboundSpecificationFactoryRegistry $registry,
    )
    {
    }

    /**
     * @param VectorInterface<OutboundFilterCriteriaInterface> $criteria
     *
     * @throws CriticalException
     */
    public function filter(OutboundMap $outboundsMap, VectorInterface $criteria): OutboundMap
    {
        $specifications = new MutableVector([]);

        foreach ($criteria as $criterion) {
            $specifications = $specifications->add($this->resolveSpecification($criterion, $outboundsMap));
        }

        return $outboundsMap->filter($specifications);
    }

    /**
     * Resolves one criteria into its specification and, if the criteria
     * implements {@see OutboundFilterCriteriaExceptOutboundsInterface} and
     * carries a non-empty except list, wraps it with
     * {@see ExceptOutboundsSpecification}.
     *
     * NOTE: this is the *only* place that knows about per-criteria excepting -
     * a new criteria class gets it for free just by implementing the
     * interface, no Specification/Factory changes required.
     *
     * @throws CriticalException
     */
    private function resolveSpecification(OutboundFilterCriteriaInterface $criterion, OutboundMap $outboundsMap): OutboundSpecificationInterface
    {
        $specification = $this->registry->resolve($criterion, $outboundsMap);

        if (!$criterion instanceof OutboundFilterCriteriaExceptOutboundsInterface) {
            return $specification;
        }

        $exceptOutbounds = $criterion->getExceptOutbounds();

        if ($exceptOutbounds === null || $exceptOutbounds->isEmpty()) {
            return $specification;
        }

        return new ExceptOutboundsSpecification($specification, $exceptOutbounds);
    }
}
