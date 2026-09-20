<?php

declare(strict_types=1);

namespace App\Commands\Shared\Options;

use App\Application\Outbound\Filter\Contract\OutboundFilterCriteriaInterface;
use Psl\Collection\VectorInterface;

/**
 * Result of resolving one prefix-variant of the outbound-filter option
 * block (see OutboundFilterOptionsTrait). Unchanged from the previous
 * version — maps 1-to-1 onto the two constructor arguments of
 * {@see \App\Application\Outbound\DTO\UseCase\FilterOutbounds\FilterOutboundsDTO}
 * that a CLI command actually needs to fill in.
 */
final readonly class OutboundFilterCriteriaBag
{
    /**
     * @param VectorInterface<OutboundFilterCriteriaInterface> $criteria
     * @param VectorInterface<string>|null $ignoreOutbounds Tags that bypass the whole pipeline (`--exceptOutbound`)
     */
    public function __construct(
        public VectorInterface  $criteria,
        public ?VectorInterface $ignoreOutbounds,
    )
    {
    }

    /**
     * True when none of the options in this group were actually provided -
     * lets a command skip calling FilterOutboundsUseCase entirely.
     */
    public function isEmpty(): bool
    {
        return $this->criteria->isEmpty() && $this->ignoreOutbounds === null;
    }
}
