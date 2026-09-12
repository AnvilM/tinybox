<?php

declare(strict_types=1);

namespace App\Application\Outbound\Export\Exporter\Security;

use App\Application\Outbound\Export\CoreType;
use App\Application\Outbound\Export\ExporterRegistry;
use App\Application\Outbound\Export\Interface\NodeExporterInterface;
use App\Domain\Outbound\VO\Security\RealitySecurityVO;

/**
 * Reality is xray-only — sing-box has no Reality client config, so this
 * exporter's {@see self::supports()} intentionally returns false for
 * {@see CoreType::SingBox}. If no other exporter handles RealitySecurityVO,
 * the registry throws {@see \App\Application\Outbound\Exception\Export\UnsupportedByCoreException}
 * when building a sing-box config for a Reality outbound — that's the
 * intended core-level restriction.
 */
final class RealitySecurityExporter implements NodeExporterInterface
{
    public function supports(object $node, CoreType $core): bool
    {
        return $node instanceof RealitySecurityVO;
    }

    public function export(object $node, CoreType $core, ExporterRegistry $registry): array
    {
        /** @var RealitySecurityVO $node */
        return match ($core) {
            CoreType::Xray => [
                'security' => 'reality',
                'realitySettings' => array_filter(
                    [
                        'serverName' => $node->getServerName()->getValue(),
                        'fingerprint' => $node->getFingerprint()?->getValue(),
                        'publicKey' => $node->getPublicKey()->getValue(),
                        'shortId' => $node->getShortId()?->getValue(),
                        'spiderX' => $node->getSpiderX()?->getValue(),
                    ],
                    static fn(mixed $value): bool => $value !== null,
                ),
            ],
            CoreType::SingBox => array_filter([
                'tls' => [
                    'enabled' => true,
                    'server_name' => $node->getServerName()->getValue(),
                    'utls' => $node->getFingerprint() === null ? null : [
                        'enabled' => true,
                        'fingerprint' => $node->getFingerprint()->getValue(),
                    ],
                    'reality' => [
                        'enabled' => true,
                        'public_key' => $node->getPublicKey()->getValue(),
                        'short_id' => $node->getShortId()?->getValue(),
                    ]
                ]
            ], static fn(mixed $value): bool => $value !== null),
        };
    }
}
