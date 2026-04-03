<?php

declare(strict_types=1);

namespace App\Domain\Interface\Outbound;

use App\Domain\Outbound\Entity\Outbound;

interface OutboundSpecificationInterface
{
    /**
     * Determines whether the given Outbound entity satisfies the specification.
     *
     * @param Outbound $outbound The outbound entity to evaluate
     *
     * @return bool True if the outbound satisfies the specification, false otherwise
     */
    public function isSatisfiedBy(Outbound $outbound): bool;
}