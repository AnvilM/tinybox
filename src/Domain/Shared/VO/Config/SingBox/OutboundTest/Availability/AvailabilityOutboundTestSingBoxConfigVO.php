<?php

declare(strict_types=1);

namespace App\Domain\Shared\VO\Config\SingBox\OutboundTest\Availability;

final readonly class AvailabilityOutboundTestSingBoxConfigVO
{
    public function __construct(
        public string                 $url,
        public AvailabilityTestMethod $method,
    )
    {
    }
}