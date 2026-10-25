<?php

declare(strict_types=1);

namespace App\Application\Outbound\Selection\Node;

/**
 * Logical AND: every child node must match.
 *
 * Never created with an empty list; a single child is used directly instead.
 */
final readonly class AllOfNode implements SelectorNode
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
