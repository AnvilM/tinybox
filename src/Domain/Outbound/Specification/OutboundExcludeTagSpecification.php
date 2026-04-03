<?php

declare(strict_types=1);

namespace App\Domain\Outbound\Specification;

use App\Domain\Interface\Outbound\OutboundSpecificationInterface;
use App\Domain\Outbound\Entity\Outbound;
use Psl\Collection\VectorInterface;

final readonly class OutboundExcludeTagSpecification implements OutboundSpecificationInterface
{
    /**
     * @param VectorInterface<string> $tags
     */
    public function __construct(
        private VectorInterface $tags,
    )
    {
    }

    public function isSatisfiedBy(Outbound $outbound): bool
    {
        foreach ($this->tags as $tag) {
            if ($outbound->getTagString() === $tag) return false;
        }

        return true;
    }
}