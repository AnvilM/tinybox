<?php

declare(strict_types=1);

namespace App\Application\Outbound\Selection\Node;

/**
 * Logical OR: at least one child node must match.
 *
 * Never created with an empty list; a single child is used directly instead.
 */
final readonly class AnyOfNode implements SelectorNode
{
    /**
     * @param list<SelectorNode> $nodes
     */
    public function __construct(
        public array $nodes,
    )
    {
    }
}
