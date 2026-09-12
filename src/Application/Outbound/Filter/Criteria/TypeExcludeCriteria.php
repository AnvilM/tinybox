<?php

declare(strict_types=1);

namespace App\Application\Outbound\Filter\Criteria;

use App\Application\Outbound\Filter\Contract\OutboundFilterCriteriaExceptOutboundsInterface;
use Psl\Collection\VectorInterface;

/**
 * Exclude outbounds whose protocol type is in the given list.
 */
final readonly class TypeExcludeCriteria implements OutboundFilterCriteriaExceptOutboundsInterface
{
    /**
     * @param VectorInterface<string> $types Raw protocol values/aliases (e.g. "vless", "ss")
     * @param VectorInterface<string>|null $exceptOutbounds Tags of outbounds that always satisfy THIS
     *                                                       criteria regardless of their type.
     *                                                       They remain fully subject to every other criteria.
     */
    public function __construct(
        public VectorInterface  $types,
        public ?VectorInterface $exceptOutbounds = null,
    )
    {
    }

    public function getExceptOutbounds(): ?VectorInterface
    {
        return $this->exceptOutbounds;
    }
}
