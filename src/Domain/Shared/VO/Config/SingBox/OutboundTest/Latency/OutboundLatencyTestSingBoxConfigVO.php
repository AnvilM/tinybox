<?php

declare(strict_types=1);

namespace App\Domain\Shared\VO\Config\SingBox\OutboundTest\Latency;

final readonly class OutboundLatencyTestSingBoxConfigVO
{
    public function __construct(
        public string            $url,
        public LatencyTestMethod $method
    )
    {
    }
}