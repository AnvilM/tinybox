<?php

declare(strict_types=1);

namespace App\Application\Outbound\Export\Interface;

use App\Application\Outbound\Export\CoreType;
use App\Application\Outbound\Export\ExporterRegistry;

/**
 * Exports one config-tree node type into an array suitable for JSON
 * serialization for a specific proxy core.
 *
 * A "node" is any domain object involved in building the config: Outbound,
 * TransportVO and its subtypes, SecurityVO and its subtypes, etc. Each node
 * is handled by exactly one matching exporter per (node class, core) pair.
 *
 * Implementations register in {@see ExporterRegistry} and are resolved
 * dynamically via {@see self::supports()} — adding a new node type or
 * constraint doesn't require modifying existing classes (Open/Closed
 * Principle).
 */
interface NodeExporterInterface
{
    /**
     * Determines if this exporter can handle the node for the given core.
     *
     * Support constraints are expressed here too: e.g. the Reality-security
     * exporter may return false for sing-box if that core doesn't support
     * Reality — then the registry finds no matching exporter and throws.
     *
     * @param object $node Config tree node (e.g. a SecurityVO instance)
     * @param CoreType $core Target proxy core
     *
     * @return bool True if this exporter can handle the node for this core
     */
    public function supports(object $node, CoreType $core): bool;

    /**
     * Builds the config fragment for a node.
     *
     * Only called after {@see self::supports()} returned true — implementations
     * don't need to re-check the node type.
     *
     * For nodes containing nested nodes (e.g. Outbound contains TransportVO
     * and SecurityVO), the implementation delegates their serialization to
     * the registry via $registry, rather than duplicating mapping logic.
     *
     * @param object $node Config tree node
     * @param CoreType $core Target proxy core
     * @param ExporterRegistry $registry Registry for recursively exporting nested nodes
     *
     * @return array<string, mixed> Config fragment as an associative array
     */
    public function export(object $node, CoreType $core, ExporterRegistry $registry): array;
}
