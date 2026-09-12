<?php

declare(strict_types=1);

namespace App\Application\Outbound\Export\Exporter\Security;

use App\Application\Outbound\Export\CoreType;
use App\Application\Outbound\Export\ExporterRegistry;
use App\Application\Outbound\Export\Interface\NodeExporterInterface;
use App\Domain\Outbound\VO\Security\TLSSecurityVO;

/**
 * Reality is xray-only — sing-box has no Reality client config, so this
 * exporter's {@see self::supports()} intentionally returns false for
 * {@see CoreType::SingBox}. If no other exporter handles RealitySecurityVO,
 * the registry throws {@see \App\Application\Outbound\Exception\Export\UnsupportedByCoreException}
 * when building a sing-box config for a Reality outbound — that's the
 * intended core-level restriction.
 */
final class TLSSecurityExporter implements NodeExporterInterface
{
    public function supports(object $node, CoreType $core): bool
    {
        return $node instanceof TLSSecurityVO;
    }

    public function export(object $node, CoreType $core, ExporterRegistry $registry): array
    {
        /** @var TLSSecurityVO $node */
        return match ($core) {
            CoreType::Xray => [
                'security' => $node->getType()->value,
                'tlsSettings' => array_filter(
                    [
                        'serverName' => $node->getServerName()->getValue(),
                        'fingerprint' => $node->getFingerprint()?->getValue(),
                        'alpn' => [$node->getAlpn()->getValue()],
                    ],
                    static fn(mixed $value): bool => $value !== null,
                ),
            ],
            CoreType::SingBox => array_filter([
                'tls' => [
                    'enabled' => true,
                    'server_name' => $node->getServerName()->getValue(),
                    'alpn' => [$node->getAlpn()->getValue()],
                    'utls' => $node->getFingerprint() === null ? null : [
                        'enabled' => true,
                        'fingerprint' => $node->getFingerprint()->getValue(),
                    ]
                ]
            ], static fn(mixed $value): bool => $value !== null),
        };
    }
}
