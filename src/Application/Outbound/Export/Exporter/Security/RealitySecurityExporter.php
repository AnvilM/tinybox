<?php

declare(strict_types=1);

namespace App\Application\Outbound\Export\Exporter\Security;

use App\Application\Outbound\DTO\Export\CoreType;
use App\Application\Outbound\Export\ExporterRegistry;
use App\Application\Outbound\Export\Interface\NodeExporterInterface;
use App\Application\Outbound\Export\Support\SingBoxUtlsFingerprint;
use App\Domain\Outbound\VO\Security\RealitySecurityVO;

/**
 * Reality is supported by both cores. A client fingerprint is mandatory for
 * it: sing-box refuses to start without uTLS ("uTLS is required by reality
 * client"), so when the link has no (or a sing-box-unknown) fingerprint
 * {@see SingBoxUtlsFingerprint::DEFAULT} is used.
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
        $fingerprint = $node->getFingerprint()?->getValue() ?? SingBoxUtlsFingerprint::DEFAULT;

        return match ($core) {
            CoreType::Xray => [
                'security' => 'reality',
                'realitySettings' => array_filter(
                    [
                        'serverName' => $node->getServerName()->getValue(),
                        'fingerprint' => $fingerprint,
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
                    'utls' => [
                        'enabled' => true,
                        'fingerprint' => SingBoxUtlsFingerprint::normalize($fingerprint) ?? SingBoxUtlsFingerprint::DEFAULT,
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
