<?php

declare(strict_types=1);

namespace App\Application\Outbound\Filter\Interface;

/**
 * Marker interface for a single, self-describing filtering intent.
 *
 * NOTE: A criteria object only carries *data* ("exclude these tags",
 * "keep only these country codes", ...). It knows nothing about *how*
 * it is turned into a domain specification - that responsibility
 * belongs to an {@see OutboundSpecificationFactoryInterface}.
 *
 * Adding a new filter rule means adding a new criteria class here,
 * without touching anything else in this contract layer.
 */
interface OutboundFilterCriteriaInterface
{
}
