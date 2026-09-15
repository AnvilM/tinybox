<?php

declare(strict_types=1);

namespace App\Application\Outbound\Export\Exporter\Outbound;

use App\Application\Outbound\DTO\Export\CoreType;
use App\Application\Outbound\Export\ExporterRegistry;
use App\Application\Outbound\Export\Interface\NodeExporterInterface;
use App\Domain\Outbound\Entity\ShadowsocksOutbound;
use App\Domain\Outbound\VO\Shadowsocks\Plugin\ShadowsocksPluginVO;

/**
 * ShadowsocksUserinfoVO and ShadowsocksPluginVO are non-polymorphic nodes,
 * so their mapping is inlined here rather than split into separate
 * {@see NodeExporterInterface} implementations. A dedicated node exporter
 * only makes sense for types with variable behavior (like TransportVO
 * or SecurityVO).
 *
 * NOTE: the plugin/plugin_opts field schema is illustrative — verify
 * against the actual xray/sing-box docs.
 */
final class ShadowsocksOutboundExporter implements NodeExporterInterface
{
    public function supports(object $node, CoreType $core): bool
    {
        return $node instanceof ShadowsocksOutbound;
    }

    public function export(object $node, CoreType $core, ExporterRegistry $registry): array
    {
        /** @var ShadowsocksOutbound $node */
        return match ($core) {
            CoreType::Xray => $this->exportXray($node),
            CoreType::SingBox => $this->exportSingBox($node),
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function exportXray(ShadowsocksOutbound $node): array
    {
        $server = array_filter(
            [
                'address' => $node->getServerString(),
                'port' => $node->getServerPortInt(),
                'method' => $node->getUserinfo()->getMethod()->value,
                'password' => $node->getUserinfo()->getPassword(),
                ...$this->buildPluginFields($node->getPlugin(), 'plugin', 'pluginOpts'),
            ],
            static fn(mixed $value): bool => $value !== null,
        );

        return [
            'tag' => $node->getTagString(),
            'protocol' => $node->getType()->value,
            'settings' => [
                'servers' => [$server],
            ],
        ];
    }

    /**
     * @return array<string, string|null>
     */
    private function buildPluginFields(?ShadowsocksPluginVO $plugin, string $pluginKey, string $pluginOptsKey): array
    {
        if ($plugin === null) {
            return [];
        }

        return array_filter(
            [
                $pluginKey => $plugin->getPlugin()->value,
                $pluginOptsKey => $plugin->getPluginOptions(),
            ],
            static fn(mixed $value): bool => $value !== null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function exportSingBox(ShadowsocksOutbound $node): array
    {
        return array_filter(
            [
                'type' => $node->getType()->value,
                'tag' => $node->getTagString(),
                'server' => $node->getServerString(),
                'server_port' => $node->getServerPortInt(),
                'method' => $node->getUserinfo()->getMethod()->value,
                'password' => $node->getUserinfo()->getPassword(),
                ...$this->buildPluginFields($node->getPlugin(), 'plugin', 'plugin_opts'),
            ],
            static fn(mixed $value): bool => $value !== null,
        );
    }
}
