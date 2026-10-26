<?php

declare(strict_types=1);

namespace App\Application\Outbound\Urltest\Specification;

use App\Domain\Interface\Outbound\OutboundSpecificationInterface;
use App\Domain\Outbound\Entity\Outbound;
use App\Domain\Outbound\Entity\UrltestOutbound;

/**
 * Keeps everything except urltest outbounds.
 *
 * Needed for xray: a balancer can not be a member of another balancer.
 */
final readonly class NoUrltestSpecification implements OutboundSpecificationInterface
{
    public function isSatisfiedBy(Outbound $outbound): bool
    {
        return !($outbound instanceof UrltestOutbound);
    }
}
