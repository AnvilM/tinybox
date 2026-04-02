<?php

declare(strict_types=1);

namespace App\Application\Shared\Utils\OutboundTest\GetIpCountyCode\SingBox\Process\DTO;

final class OutboundIpDTO
{

    private ?string $ip = null;

    public function __construct(
        public readonly string $outboundTag,
        public readonly string $outboundIp
    )
    {
    }

    public function getIp(): ?string
    {
        return $this->ip;
    }

    public function setIp(string $ip): void
    {
        $this->ip = $ip;
    }
}