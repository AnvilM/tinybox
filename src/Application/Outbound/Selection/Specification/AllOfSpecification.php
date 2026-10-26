<?php

declare(strict_types=1);

namespace App\Application\Outbound\Selection\Specification;

use App\Domain\Interface\Outbound\OutboundSpecificationInterface;
use App\Domain\Outbound\Entity\Outbound;

final readonly class AllOfSpecification implements OutboundSpecificationInterface
{
    /**
     * @param list<OutboundSpecificationInterface> $specifications
     */
    public function __construct(
        private array $specifications,
    )
    {
    }

    public function isSatisfiedBy(Outbound $outbound): bool
    {
        foreach ($this->specifications as $specification) {
            if (!$specification->isSatisfiedBy($outbound)) {
                return false;
            }
        }

        return true;
    }
}
