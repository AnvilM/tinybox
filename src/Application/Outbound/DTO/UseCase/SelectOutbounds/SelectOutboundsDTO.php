<?php

declare(strict_types=1);

namespace App\Application\Outbound\DTO\UseCase\SelectOutbounds;

use App\Application\Outbound\Selection\Node\SelectorNode;
use App\Domain\Interface\Outbound\OutboundSpecificationInterface;
use App\Domain\Outbound\Collection\OutboundMap;
use Psl\Collection\Vector;
use Psl\Collection\VectorInterface;

final readonly class SelectOutboundsDTO
{
    /**
     * @param OutboundMap $outboundsMap Outbounds to select from
     * @param SelectorNode|null $selector Selection tree, null - select everything
     * @param VectorInterface<OutboundSpecificationInterface> $constraints Technical restrictions, always applied
     *                                                                     together with the selector (logical AND)
     */
    public function __construct(
        public OutboundMap     $outboundsMap,
        public ?SelectorNode   $selector = null,
        public VectorInterface $constraints = new Vector([]),
    )
    {
    }
}
