<?php

declare(strict_types=1);

namespace App\Application\Outbound\Export;

use App\Application\Outbound\Exception\Export\UnsupportedByCoreException;
use App\Application\Outbound\Export\Interface\NodeExporterInterface;

/**
 * Resolves and invokes the right {@see NodeExporterInterface} for a config
 * tree node (Outbound, TransportVO, SecurityVO, etc).
 *
 * The only place that dispatches "node class + core -> exporter". Exporters
 * only know about each other via this registry, passed to them in
 * {@see NodeExporterInterface::export()} — this lets composite nodes
 * (Outbound) recursively delegate serialization of nested nodes without
 * knowing their concrete implementations.
 */
final readonly class ExporterRegistry
{
    /**
     * @param iterable<NodeExporterInterface> $exporters All registered exporters.
     *                                                    Order only matters if multiple exporters
     *                                                    are registered for the same node+core —
     *                                                    the first match is used.
     */
    public function __construct(private iterable $exporters)
    {
    }

    /**
     * Builds a config fragment for an optional node, returning null instead
     * of throwing if the node is absent or unsupported by the core.
     *
     * Only use this for nodes whose silent absence is safe and doesn't
     * change connection semantics (e.g. decorative/optional fields). For
     * security-critical nodes use {@see self::export()} instead, so
     * unsupported cases aren't silently ignored.
     *
     * @param object|null $node Config tree node, or null
     * @param CoreType $core Target proxy core
     *
     * @return array<string, mixed>|null
     */
    public function tryExport(?object $node, CoreType $core): ?array
    {
        if ($node === null) {
            return null;
        }

        try {
            return $this->export($node, $core);
        } catch (UnsupportedByCoreException) {
            return null;
        }
    }

    /**
     * Builds a config fragment for a node. Throws if no exporter supports
     * the node+core combination.
     *
     * Use for nodes whose absence is unacceptable (security, required
     * protocol fields) — where an unsupported node means the whole parent
     * config can't be correctly built and the error must surface to the caller.
     *
     * @param object $node Config tree node
     * @param CoreType $core Target proxy core
     *
     * @return array<string, mixed>
     *
     * @throws UnsupportedByCoreException If the node isn't supported by the given core
     */
    public function export(object $node, CoreType $core): array
    {
        foreach ($this->exporters as $exporter) {
            if ($exporter->supports($node, $core)) {
                return $exporter->export($node, $core, $this);
            }
        }

        throw UnsupportedByCoreException::forNode($node, $core);
    }

    /**
     * Checks node support by the core without building the config fragment.
     *
     * Useful when whether to include a node must be decided before
     * calling export().
     *
     * @param object $node Config tree node
     * @param CoreType $core Target proxy core
     */
    public function supports(object $node, CoreType $core): bool
    {
        foreach ($this->exporters as $exporter) {
            if ($exporter->supports($node, $core)) {
                return true;
            }
        }

        return false;
    }
}
