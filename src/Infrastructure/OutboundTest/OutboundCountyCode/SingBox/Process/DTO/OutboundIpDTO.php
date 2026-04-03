<?php

declare(strict_types=1);

namespace App\Infrastructure\OutboundTest\OutboundCountyCode\SingBox\Process\DTO;

use App\Domain\Outbound\Entity\Outbound;

final class OutboundIpDTO
{

    private ?string $ip = null;

    public function __construct(
        public readonly Outbound $outbound,
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