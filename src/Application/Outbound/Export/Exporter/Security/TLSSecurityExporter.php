<?php

declare(strict_types=1);

namespace App\Application\Outbound\Export\Exporter\Security;

use App\Application\Outbound\DTO\Export\CoreType;
use App\Application\Outbound\Export\ExporterRegistry;
use App\Application\Outbound\Export\Interface\NodeExporterInterface;
use App\Application\Outbound\Export\Support\SingBoxUtlsFingerprint;
use App\Domain\Outbound\VO\Security\TLSSecurityVO;

/**
 * TLS is supported by both cores. For sing-box uTLS is enabled only with a
 * fingerprint it knows, because an unknown one makes it fail at startup;
 * such a fingerprint is skipped (plain TLS is used instead).
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
        $singBoxFingerprint = SingBoxUtlsFingerprint::normalize($node->getFingerprint()?->getValue());

        return match ($core) {
            CoreType::Xray => [
                'security' => $node->getType()->value,
                'tlsSettings' => array_filter(
                    [
                        'serverName' => $node->getServerName()->getValue(),
                        'fingerprint' => $node->getFingerprint()?->getValue(),
                        'alpn' => $this->alpnList($node),
                        'allowInsecure' => $node->getInsecure() === null ? null : $node->getInsecure(),
                        'verifyPeerCertByName' => $node->getVerifyPeerCertByName()?->getValue(),
                    ],
                    static fn(mixed $value): bool => $value !== null,
                ),
            ],
            CoreType::SingBox => array_filter([
                'tls' => array_filter([
                    'enabled' => true,
                    'server_name' => $node->getServerName()->getValue(),
                    'alpn' => $this->alpnList($node),
                    'insecure' => $node->getInsecure() === null ? null : $node->getInsecure(),
                    'utls' => $singBoxFingerprint === null ? null : [
                        'enabled' => true,
                        'fingerprint' => $singBoxFingerprint,
                    ]
                ], static fn(mixed $value): bool => $value !== null)
            ], static fn(mixed $value): bool => $value !== null),
        };
    }

    /**
     * ALPN is stored as a comma separated string ("h2,http/1.1") but both cores expect a list
     *
     * @return string[]|null
     */
    private function alpnList(TLSSecurityVO $node): ?array
    {
        return $node->getAlpn() === null ? null : explode(',', $node->getAlpn()->getValue());
    }
}