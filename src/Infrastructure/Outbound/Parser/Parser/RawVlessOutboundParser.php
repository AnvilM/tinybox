<?php

declare(strict_types=1);

namespace App\Infrastructure\Outbound\Parser\Parser;

use App\Domain\Outbound\VO\RawOutbound\RawVlessOutboundVO;
use App\Domain\TLS\VO\RawReality;
use App\Domain\TLS\VO\RawTLS;
use App\Domain\TLS\VO\RawUTLS;
use InvalidArgumentException;
use Throwable;

final readonly class RawVlessOutboundParser
{
    /**
     * Parse vless outbound as JSON decoded array to raw vless outbound value object
     *
     * @param array $jsonDecodedVlessOutbound Vless outbound as JSON decoded array
     *
     * @return RawVlessOutboundVO Raw vless outbound value object
     *
     * @throws InvalidArgumentException
     */
    public static function handle(array $jsonDecodedVlessOutbound): RawVlessOutboundVO
    {
        try {
            return new RawVlessOutboundVO(
                $jsonDecodedVlessOutbound['type'],
                $jsonDecodedVlessOutbound['tag'],
                $jsonDecodedVlessOutbound['server'],
                $jsonDecodedVlessOutbound['server_port'],
                $jsonDecodedVlessOutbound['uuid'] ?? null,
                $jsonDecodedVlessOutbound['flow'] ?? null,
                new RawTLS(
                    $jsonDecodedVlessOutbound['tls']['server_name'] ?? null,
                    isset($jsonDecodedVlessOutbound['tls']['reality']) ? new RawReality(
                        $jsonDecodedVlessOutbound['tls']['reality']['public_key'] ?? null,
                        $jsonDecodedVlessOutbound['tls']['reality']['short_id'] ?? null,
                        $jsonDecodedVlessOutbound['tls']['reality']['enabled']
                    ) : null,
                    isset($jsonDecodedVlessOutbound['tls']['utls']) ? new RawUTLS(
                        $jsonDecodedVlessOutbound['tls']['utls']['fingerprint'] ?? null,
                        $jsonDecodedVlessOutbound['tls']['utls']['enabled']
                    ) : null,
                    $jsonDecodedVlessOutbound['tls']['enabled']
                )
            );
        } catch (Throwable) {
            throw new InvalidArgumentException();
        }
    }
}