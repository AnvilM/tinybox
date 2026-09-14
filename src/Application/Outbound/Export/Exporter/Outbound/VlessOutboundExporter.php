<?php

declare(strict_types=1);

namespace App\Application\Outbound\Export\Exporter\Outbound;

use App\Application\Outbound\DTO\Export\CoreType;
use App\Application\Outbound\Export\ExporterRegistry;
use App\Application\Outbound\Export\Interface\NodeExporterInterface;
use App\Domain\Outbound\Entity\VlessOutbound;

/**
 * VlessOutbound is representable in both cores. Nested-node constraints
 * (e.g. Reality only for xray) aren't duplicated here — they're
 * encapsulated in the respective Security/Transport exporters and
 * propagate via {@see ExporterRegistry::export()}.
 *
 * NOTE: the streamSettings/transport schema is illustrative — verify
 * against the actual xray/sing-box docs.
 */
final class VlessOutboundExporter implements NodeExporterInterface
{
    public function supports(object $node, CoreType $core): bool
    {
        return $node instanceof VlessOutbound;
    }

    public function export(object $node, CoreType $core, ExporterRegistry $registry): array
    {
        /** @var VlessOutbound $node */
        return match ($core) {
            CoreType::Xray => $this->exportXray($node, $registry),
            CoreType::SingBox => $this->exportSingBox($node, $registry),
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function exportXray(VlessOutbound $node, ExporterRegistry $registry): array
    {
        $user = array_filter(
            [
                'id' => $node->getUUID(),
                'flow' => $node->getFlow(),
                'encryption' => 'none',
            ],
            static fn(mixed $value): bool => $value !== null,
        );

        $streamSettings = $this->buildXrayStreamSettings($node, $registry);

        return array_filter(
            [
                'tag' => $node->getTagString(),
                'protocol' => $node->getType()->value,
                'settings' => [
                    'vnext' => [
                        [
                            'address' => $node->getServer(),
                            'port' => $node->getServerPort(),
                            'users' => [$user],
                        ],
                    ],
                ],
                'streamSettings' => $streamSettings !== [] ? $streamSettings : null,
            ],
            static fn(mixed $value): bool => $value !== null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function buildXrayStreamSettings(VlessOutbound $node, ExporterRegistry $registry): array
    {
        $streamSettings = [];

        // Security is critical: if set but unsupported by the core (e.g.
        // Reality for sing-box), let the registry exception propagate
        // instead of swallowing it — otherwise the config would silently
        // lose its security params.
        if ($node->getSecurity() !== null) {
            $streamSettings += $registry->export($node->getSecurity(), CoreType::Xray);
        }

        if ($node->getTransport() !== null) {
            $streamSettings += $registry->export($node->getTransport(), CoreType::Xray);
        }

        return $streamSettings;
    }

    /**
     * @return array<string, mixed>
     */
    private function exportSingBox(VlessOutbound $node, ExporterRegistry $registry): array
    {
        $config = array_filter(
            [
                'type' => $node->getType()->value,
                'tag' => $node->getTagString(),
                'server' => $node->getServer(),
                'server_port' => $node->getServerPort(),
                'uuid' => $node->getUUID(),
                'flow' => $node->getFlow(),
            ],
            static fn(mixed $value): bool => $value !== null,
        );

        if ($node->getSecurity() !== null) {
            $config += $registry->export($node->getSecurity(), CoreType::SingBox);
        }

        if ($node->getTransport() !== null) {
            $config['transport'] = $registry->export($node->getTransport(), CoreType::SingBox);
        }

        return $config;
    }
}
