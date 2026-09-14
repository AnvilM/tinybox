<?php

declare(strict_types=1);

namespace App\Application\Outbound\Export\Exporter\Transport;

use App\Application\Outbound\DTO\Export\CoreType;
use App\Application\Outbound\Export\ExporterRegistry;
use App\Application\Outbound\Export\Interface\NodeExporterInterface;
use App\Domain\Outbound\VO\Transport\XHTTPTransportVO;

/**
 * WebSocket transport is supported by both cores but with different JSON
 * shapes: xray expects a nested `wsSettings` inside `streamSettings`,
 * sing-box a flat `transport` object with a `type` field.
 */
final class XHTTPTransportExporter implements NodeExporterInterface
{
    public function supports(object $node, CoreType $core): bool
    {
        return $node instanceof XHTTPTransportVO && $core === CoreType::Xray;
    }

    public function export(object $node, CoreType $core, ExporterRegistry $registry): array
    {
        /** @var XHTTPTransportVO $node */
        return [
            'network' => $node->getType()->value,
            'xhttpSettings' => [
                'mode' => $node->getMode()->getValue(),
                'host' => $node->getHost()->getValue(),
                'path' => $node->getPath()->getValue(),
                'extra' => $node->getExtra(),
            ],
        ];
    }
}
