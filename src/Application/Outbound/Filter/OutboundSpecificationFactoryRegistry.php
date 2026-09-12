<?php

declare(strict_types=1);

namespace App\Application\Outbound\Filter;

use App\Application\Outbound\Exception\Filter\UnsupportedOutboundFilterCriteriaException;
use App\Application\Outbound\Filter\Interface\OutboundFilterCriteriaInterface;
use App\Application\Outbound\Filter\Interface\OutboundSpecificationFactoryInterface;
use App\Domain\Interface\Outbound\OutboundSpecificationInterface;
use App\Domain\Outbound\Collection\OutboundMap;
use App\Domain\Shared\Exception\CriticalException;
use Psl\Collection\Vector;
use Psl\Collection\VectorInterface;

/**
 * Central place that knows every available filter rule.
 *
 * To add a new filter rule:
 *  1. Add a new `*Criteria` class (Filter/Criteria).
 *  2. Add a new specification implementing the domain
 *     `OutboundSpecificationInterface` (Filter/Specification).
 *  3. Add a new `*SpecificationFactory` connecting the two
 *     (Filter/SpecificationFactory).
 *  4. Register the factory instance in this registry's constructor (or via
 *     your DI container, e.g. tagged services).
 *
 * No existing class needs to change - this is the Open/Closed part of the
 * design.
 */
final readonly class OutboundSpecificationFactoryRegistry
{
    /**
     * @var VectorInterface<OutboundSpecificationFactoryInterface>
     */
    private VectorInterface $factories;

    /**
     * @param iterable<OutboundSpecificationFactoryInterface> $factories
     */
    public function __construct(iterable $factories)
    {
        $this->factories = new Vector(is_array($factories) ? $factories : iterator_to_array($factories));
    }

    /**
     * @throws CriticalException
     */
    public function resolve(OutboundFilterCriteriaInterface $criteria, OutboundMap $outboundsMap): OutboundSpecificationInterface
    {
        foreach ($this->factories as $factory) {
            if ($factory->supports($criteria)) {
                return $factory->create($criteria, $outboundsMap);
            }
        }

        throw new UnsupportedOutboundFilterCriteriaException($criteria);
    }
}
