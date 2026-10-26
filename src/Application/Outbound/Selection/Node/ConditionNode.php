<?php

declare(strict_types=1);

namespace App\Application\Outbound\Selection\Node;

/**
 * Leaf of the tree: `property operator operand[, operand...]`.
 *
 * NOTE: only the operators `=` and `~` exist at this level. `!=` and `!~`
 * are expressed by the parser as NotNode(ConditionNode).
 */
final readonly class ConditionNode implements SelectorNode
{
    /**
     * @param string $property Property name, lower-case
     * @param string $operator Either '=' or '~'
     * @param list<string> $operands Raw values as typed by the user (quotes already removed)
     * @param string $source Whole source expression, used for error messages
     * @param int $position Character offset of the property name inside $source
     */
    public function __construct(
        public string $property,
        public string $operator,
        public array  $operands,
        public string $source,
        public int    $position,
    )
    {
    }
}
