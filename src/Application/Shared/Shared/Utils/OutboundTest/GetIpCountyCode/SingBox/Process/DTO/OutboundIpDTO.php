<?php

declare(strict_types=1);

namespace App\Application\Shared\Shared\Utils\OutboundTest\GetIpCountyCode\SingBox\Process\DTO;

final readonly class OutboundIpDTO
{
    public function __construct(
        public string $outboundTag,
        public string $ip,
    )
    {
    }
}