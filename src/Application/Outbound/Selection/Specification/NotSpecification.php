<?php

declare(strict_types=1);

namespace App\Application\Outbound\Selection\Specification;

use App\Domain\Interface\Outbound\OutboundSpecificationInterface;
use App\Domain\Outbound\Entity\Outbound;

final readonly class NotSpecification implements OutboundSpecificationInterface
{
    public function __construct(
        private OutboundSpecificationInterface $specification,
    )
    {
    }

    public function isSatisfiedBy(Outbound $outbound): bool
    {
        return !$this->specification->isSatisfiedBy($outbound);
    }
}
