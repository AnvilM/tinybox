<?php

declare(strict_types=1);

namespace App\Application\Outbound\Export;

use App\Application\Outbound\Export\Exporter\Outbound\ShadowsocksOutboundExporter;
use App\Application\Outbound\Export\Exporter\Outbound\VlessOutboundExporter;
use App\Application\Outbound\Export\Exporter\Security\RealitySecurityExporter;
use App\Application\Outbound\Export\Exporter\Transport\WebSocketTransportExporter;
use App\Application\Outbound\Export\Interface\NodeExporterInterface;
use App\Application\Outbound\Export\Specification\VisionFlowRequiresSecureSecuritySpecification;

/**
 * Assembles an {@see OutboundConfigExporterInterface} with all built-in
 * exporters/specifications, out of the box — no DI container needed.
 *
 * With Symfony (or another DI container with interface autoconfigure),
 * prefer registering exporters via the container instead (see
 * Resources/config/outbound_export.yaml) — then implementing
 * {@see NodeExporterInterface} is enough for the container to pick it
 * up automatically, without touching this factory.
 *
 * This factory is the entry point for projects without such a container,
 * and a convenient starting point for manual assembly in scripts/tests.
 */
final class ExporterRegistryFactory
{

    public static function createDefaultExporter(): OutboundConfigExporterInterface
    {
        return new OutboundConfigExporter(
            self::createDefault(),
            [
                new VisionFlowRequiresSecureSecuritySpecification(),
            ],
        );
    }

    public static function createDefault(): ExporterRegistry
    {
        return new ExporterRegistry([
            new VlessOutboundExporter(),
            new ShadowsocksOutboundExporter(),
            new RealitySecurityExporter(),
            new WebSocketTransportExporter()
        ]);
    }
}
