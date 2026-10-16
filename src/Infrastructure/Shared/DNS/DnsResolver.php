<?php

declare(strict_types=1);

namespace App\Infrastructure\Shared\DNS;

use App\Domain\Shared\Ports\Config\ConfigInstancePort;
use App\Domain\Shared\Ports\DNS\DnsResolverPort;
use App\Domain\Shared\Ports\Http\HttpPort;
use Throwable;

final readonly class DnsResolver implements DnsResolverPort
{
    private const array TYPES = [
        1 => 'A',
        28 => 'AAAA',
    ];

    public function __construct(
        private HttpPort           $http,
        private ConfigInstancePort $configInstancePort,
    )
    {
    }

    public function resolveDoH(string $host): ?string
    {
        foreach ($this->configInstancePort->get()->commonConfig->dnsCommonConfig->endpoints as $endpoint) {
            foreach (self::TYPES as $typeCode => $typeName) {
                $ip = $this->query($endpoint, $host, $typeCode, $typeName);

                if ($ip !== null) {
                    return $ip;
                }
            }
        }

        return null;
    }

    private function query(string $endpoint, string $host, int $typeCode, string $typeName): ?string
    {
        $url = $endpoint . '?' . http_build_query(['name' => $host, 'type' => $typeName]);

        try {
            $body = $this->http
                ->get($this->configInstancePort->get()->commonConfig->dnsCommonConfig->resolveTimeout, $url, ['Accept' => 'application/dns-json'])
                ->body?->readAll();

            if ($body === null || $body === '') {
                return null;
            }

            /** @var array{Status?: int, Answer?: list<array{type: int, data: string}>} $data */
            $data = json_decode($body, true, flags: JSON_THROW_ON_ERROR);
        } catch (Throwable) {
            return null;
        }

        if (($data['Status'] ?? -1) !== 0) {
            return null;
        }

        foreach ($data['Answer'] ?? [] as $answer) {
            if (($answer['type'] ?? null) === $typeCode
                && filter_var($answer['data'] ?? '', FILTER_VALIDATE_IP) !== false
            ) {
                return $answer['data'];
            }
        }

        return null;
    }
}