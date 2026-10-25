<?php

declare(strict_types=1);

namespace App\Application\Outbound\Selection\Node;

/**
 * Marker for every node of a selection tree.
 *
 * Nodes are pure data: they know nothing about outbounds, properties or
 * operators. Meaning is attached later by SelectorCompiler.
 */
interface SelectorNode
{
}
