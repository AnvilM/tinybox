<?php

declare(strict_types=1);

namespace App\Application\Outbound\Filter\Criteria;

use App\Application\Outbound\Filter\Interface\OutboundFilterCriteriaInterface;
use Psl\Collection\VectorInterface;

/**
 * Exclude outbounds whose tag is in the given list.
 */
final readonly class TagExcludeCriteria implements OutboundFilterCriteriaInterface
{
    /**
     * @param VectorInterface<string> $tags
     */
    public function __construct(
        public VectorInterface $tags,
    )
    {
    }
}
