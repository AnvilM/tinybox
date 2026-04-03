<?php

declare(strict_types=1);

namespace App\Domain\Outbound\Specification;

use App\Domain\Interface\Outbound\OutboundSpecificationInterface;
use App\Domain\Outbound\Entity\Outbound;
use App\Domain\Outbound\VO\OutboundTypeVO;
use Psl\Collection\VectorInterface;

final readonly class OutboundTypeSpecification implements OutboundSpecificationInterface
{
    /**
     * @param VectorInterface<OutboundTypeVO> $outboundTypes
     */
    public function __construct(
        private VectorInterface $outboundTypes,
    )
    {
    }

    public function isSatisfiedBy(Outbound $outbound): bool
    {
        foreach ($this->outboundTypes as $outboundType) {
            if ($outboundType === $outbound->getType()) return false;
        }

        return true;
    }
}