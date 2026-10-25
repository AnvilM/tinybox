<?php

declare(strict_types=1);

namespace App\Application\Outbound\Selection;

use App\Application\Outbound\Selection\Node\AllOfNode;
use App\Application\Outbound\Selection\Node\AnyOfNode;
use App\Application\Outbound\Selection\Node\NotNode;
use App\Application\Outbound\Selection\Node\SelectorNode;

/**
 * Composes one selection tree out of "select" and "exclude" node lists.
 *
 * Pure logic: knows nothing about flags, parsing or commands.
 */
final readonly class SelectorComposer
{
    /**
     * AllOf( select1, ..., selectN, Not( AnyOf( exclude1, ..., excludeM ) ) )
     *
     * A single node is used as is, without a redundant AllOf/AnyOf wrapper.
     *
     * @param list<SelectorNode> $selects  combined with AND
     * @param list<SelectorNode> $excludes combined with OR inside the exclusion, which is then subtracted
     *
     * @return SelectorNode|null null - both lists are empty, everything is selected
     */
    public function compose(array $selects, array $excludes): ?SelectorNode
    {
        $nodes = array_values($selects);
        $excludes = array_values($excludes);

        if ($excludes !== []) {
            $nodes[] = new NotNode(count($excludes) === 1 ? $excludes[0] : new AnyOfNode($excludes));
        }

        return match (count($nodes)) {
            0 => null,
            1 => $nodes[0],
            default => new AllOfNode($nodes),
        };
    }
}
