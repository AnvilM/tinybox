<?php

declare(strict_types=1);

namespace App\Domain\Shared\VO\Config\SingBox\OutboundTest\FetchIp;

final readonly class FetchIpOutboundTestSingBoxConfigVO
{
    public function __construct(
        public string $geoIpDatabase,
        public string $url
    )
    {
    }
}