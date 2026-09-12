<?php

declare(strict_types=1);

namespace App\Application\Outbound\Export;

use App\Application\Outbound\Exception\Export\IncompatibleOutboundException;
use App\Application\Outbound\Exception\Export\UnsupportedByCoreException;
use App\Domain\Outbound\Entity\Outbound;

/**
 * Entry point for building an outbound config array for a specific
 * proxy core (sing-box, xray, ...).
 */
interface OutboundConfigExporterInterface
{
    /**
     * Builds the config for a single outbound for the given core.
     *
     * @param Outbound $outbound Outbound to build the config for
     * @param CoreType $core Target proxy core
     *
     * @return array<string, mixed> Associative array ready for json_encode()
     *
     * @throws UnsupportedByCoreException   If the outbound or one of its
     *                                       required nested nodes isn't
     *                                       supported by the given core
     * @throws IncompatibleOutboundException If a cross-field compatibility
     *                                       spec is violated
     */
    public function export(Outbound $outbound, CoreType $core): array;
}
