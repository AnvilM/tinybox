<?php

declare(strict_types=1);

namespace App\Application\Outbound\Filter\Interface;

use App\Domain\Interface\Outbound\OutboundSpecificationInterface;
use App\Domain\Outbound\Collection\OutboundMap;
use App\Domain\Shared\Exception\CriticalException;

/**
 * Strategy responsible for turning one {@see OutboundFilterCriteriaInterface}
 * into a ready-to-use domain specification.
 *
 * Each concrete filter rule (tag exclusion, country code, type, ...) has
 * exactly one factory implementing this interface. Factories are collected
 * by {@see \App\Application\Outbound\Filter\OutboundSpecificationFactoryRegistry},
 * which picks the right one for a given criteria at runtime.
 *
 * NOTE: The full outbounds map is passed to `create()` because some rules
 * (e.g. country code based ones) need it to resolve extra data through a
 * port before the specification can be built.
 */
interface OutboundSpecificationFactoryInterface
{
    /**
     * Whether this factory knows how to handle the given criteria.
     */
    public function supports(OutboundFilterCriteriaInterface $criteria): bool;

    /**
     * Build the domain specification described by the given criteria.
     *
     * @throws CriticalException If the specification can't be built (e.g. an
     *                           external port call failed).
     */
    public function create(OutboundFilterCriteriaInterface $criteria, OutboundMap $outboundsMap): OutboundSpecificationInterface;
}
