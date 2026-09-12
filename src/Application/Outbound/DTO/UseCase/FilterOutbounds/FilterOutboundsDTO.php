<?php

declare(strict_types=1);

namespace App\Application\Outbound\DTO\UseCase\FilterOutbounds;

use App\Application\Outbound\Filter\Interface\OutboundFilterCriteriaInterface;
use App\Domain\Outbound\Collection\OutboundMap;
use Psl\Collection\Vector;
use Psl\Collection\VectorInterface;

final readonly class FilterOutboundsDTO
{
    /**
     * @param OutboundMap $outboundsMap Outbounds to filter
     * @param VectorInterface<OutboundFilterCriteriaInterface> $criteria Filter rules to apply, order does not matter -
     *                                                                   every criteria must be satisfied (logical AND)
     * @param VectorInterface<string>|null $ignoreOutbounds Tags of outbounds that must always be kept in the result,
     *                                                       regardless of $criteria
     */
    public function __construct(
        public OutboundMap      $outboundsMap,
        public VectorInterface  $criteria = new Vector([]),
        public ?VectorInterface $ignoreOutbounds = null,
    )
    {
    }
}
