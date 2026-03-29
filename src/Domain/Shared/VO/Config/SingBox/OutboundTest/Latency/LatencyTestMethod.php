<?php

declare(strict_types=1);

namespace App\Domain\Shared\VO\Config\SingBox\OutboundTest\Latency;

enum LatencyTestMethod: string
{
    case PROXY_GET = "proxy_get";

    case TCP_PING = "tcp_ping";
}