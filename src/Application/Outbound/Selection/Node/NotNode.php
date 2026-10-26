<?php

declare(strict_types=1);

namespace App\Application\Outbound\Selection\Node;

/**
 * Logical NOT: inverts the child node.
 */
final readonly class NotNode implements SelectorNode
{
    public function __construct(
        public SelectorNode $node,
    )
    {
    }
}
