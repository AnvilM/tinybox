<?php

declare(strict_types=1);

namespace App\Application\Outbound\Export\Support;

/**
 * uTLS fingerprints known to sing-box.
 *
 * sing-box refuses to start with an unknown fingerprint ("unknown uTLS
 * fingerprint"), while subscriptions may contain values that only xray
 * understands, so anything outside this list must not reach the sing-box config.
 */
final class SingBoxUtlsFingerprint
{
    public const string DEFAULT = 'chrome';

    private const array SUPPORTED = [
        'chrome', 'firefox', 'edge', 'safari', '360', 'qq', 'ios', 'android', 'random', 'randomized',
    ];

    /**
     * @return string|null Lowercased fingerprint or null if it is empty / unsupported by sing-box
     */
    public static function normalize(?string $fingerprint): ?string
    {
        if ($fingerprint === null) return null;

        $fingerprint = strtolower(trim($fingerprint));

        return in_array($fingerprint, self::SUPPORTED, true) ? $fingerprint : null;
    }
}
