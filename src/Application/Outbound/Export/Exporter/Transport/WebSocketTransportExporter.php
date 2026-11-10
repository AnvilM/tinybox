<?php

declare(strict_types=1);

namespace App\Application\Outbound\Export\Exporter\Transport;

use App\Application\Outbound\DTO\Export\CoreType;
use App\Application\Outbound\Export\ExporterRegistry;
use App\Application\Outbound\Export\Interface\NodeExporterInterface;
use App\Domain\Outbound\VO\Transport\WebSocketTransportVO;

/**
 * WebSocket transport is supported by both cores but with different JSON
 * shapes: xray expects a nested `wsSettings` inside `streamSettings`,
 * sing-box a flat `transport` object with a `type` field.
 */
final class WebSocketTransportExporter implements NodeExporterInterface
{
    public function supports(object $node, CoreType $core): bool
    {
        return $node instanceof WebSocketTransportVO;
    }

    public function export(object $node, CoreType $core, ExporterRegistry $registry): array
    {
        /** @var WebSocketTransportVO $node */
        $path = $node->getPath()->getValue();

        /**
         * "Host" header is optional: without it the core uses the server address / SNI
         */
        $headers = $node->getHost() !== null
            ? ['Host' => $node->getHost()->getValue()]
            : null;

        return match ($core) {
            CoreType::Xray => [
                'network' => $node->getType()->value,
                'wsSettings' => array_filter([
                    'path' => $path,
                    'headers' => $headers,
                ], static fn(mixed $v): bool => $v !== null),
            ],
            CoreType::SingBox => array_filter([
                'type' => $node->getType()->value,
                'path' => $path,
                'headers' => $headers,
            ], static fn(mixed $v): bool => $v !== null),
        };
    }
}
