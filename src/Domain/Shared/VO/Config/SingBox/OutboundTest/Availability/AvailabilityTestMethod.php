<?php

declare(strict_types=1);

namespace App\Domain\Shared\VO\Config\SingBox\OutboundTest\Availability;

enum AvailabilityTestMethod: string
{
    case PROXY_GET = "proxy.get";

    case IP_PING = "ip.ping";
}