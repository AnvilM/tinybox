<?php

declare(strict_types=1);

namespace App\Application\Outbound\Filter;

use App\Application\Outbound\Filter\Interface\OutboundFilterCriteriaInterface;
use App\Domain\Outbound\Collection\OutboundMap;
use App\Domain\Shared\Exception\CriticalException;
use Psl\Collection\Vector;
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
        $specifications = new Vector([]);

        foreach ($criteria as $criterion) {
            $specifications = $specifications->add($this->registry->resolve($criterion, $outboundsMap));
        }

        return $outboundsMap->filter($specifications);
    }
}
