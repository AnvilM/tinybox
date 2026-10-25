<?php

declare(strict_types=1);

namespace App\Application\Outbound\Export\Exporter\Outbound;

use App\Application\Outbound\DTO\Export\CoreType;
use App\Application\Outbound\Export\ExporterRegistry;
use App\Application\Outbound\Export\Interface\NodeExporterInterface;
use App\Domain\Outbound\Entity\UrltestOutbound;

/**
 * UrltestOutbound is an outbound only for sing-box.
 *
 * NOTE: for xray urltest is not an outbound of the config: balancer is placed
 * into routing.balancers (with the common observatory) by the config creation.
 */
final class UrltestOutboundExporter implements NodeExporterInterface
{
    public function supports(object $node, CoreType $core): bool
    {
        return $node instanceof UrltestOutbound && $core === CoreType::SingBox;
    }

    public function export(object $node, CoreType $core, ExporterRegistry $registry): array
    {
        /** @var UrltestOutbound $node */
        $outbound = $node->getParameters()->getSingBox();

        $outbound['tag'] = $node->getTagString();
        $outbound['outbounds'] = array_merge(
            $outbound['outbounds'] ?? [],
            $node->getMembers()->getTagsString()->toArray()
        );

        return $outbound;
    }
}
