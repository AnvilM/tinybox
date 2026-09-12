<?php

declare(strict_types=1);

namespace App\Application\Outbound\Export\Exporter\Transport;

use App\Application\Outbound\Export\CoreType;
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
        return match ($core) {
            CoreType::Xray => [
                'network' => $node->getType()->value,
                'wsSettings' => [
                    'path' => $node->getPath()->getValue(),
                    'headers' => [
                        'Host' => $node->getHost()->getValue(),
                    ],
                ],
            ],
            CoreType::SingBox => [
                'type' => $node->getType()->value,
                'path' => $node->getPath()->getValue(),
                'headers' => [
                    'Host' => $node->getHost()->getValue(),
                ],
            ],
        };
    }
}
