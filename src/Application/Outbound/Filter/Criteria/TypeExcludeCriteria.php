<?php

declare(strict_types=1);

namespace App\Application\Outbound\Filter\Criteria;

use App\Application\Outbound\Filter\Interface\OutboundFilterCriteriaInterface;
use Psl\Collection\VectorInterface;

/**
 * Exclude outbounds whose protocol type is in the given list.
 */
final readonly class TypeExcludeCriteria implements OutboundFilterCriteriaInterface
{
    /**
     * @param VectorInterface<string> $types Raw protocol values/aliases (e.g. "vless", "ss")
     */
    public function __construct(
        public VectorInterface $types,
    )
    {
    }
}
