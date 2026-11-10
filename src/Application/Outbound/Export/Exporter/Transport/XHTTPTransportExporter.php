<?php

declare(strict_types=1);

namespace App\Application\Outbound\Export\Exporter\Transport;

use App\Application\Outbound\DTO\Export\CoreType;
use App\Application\Outbound\Export\ExporterRegistry;
use App\Application\Outbound\Export\Interface\NodeExporterInterface;
use App\Domain\Outbound\VO\Transport\XHTTPTransportVO;

/**
 * XHTTP transport is supported by xray only: it expects a nested
 * `xhttpSettings` inside `streamSettings`.
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
            /**
             * Optional fields are omitted so the core uses its defaults
             */
            'xhttpSettings' => array_filter([
                'mode' => $node->getMode()?->getValue(),
                'host' => $node->getHost()?->getValue(),
                'path' => $node->getPath()?->getValue(),
                'extra' => $node->getExtra(),
            ], static fn(mixed $v): bool => $v !== null),
        ];
    }
}
