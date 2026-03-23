<?php

declare(strict_types=1);

namespace App\Domain\Shared\VO\Outbound\Transport;

enum TransportTypeVO: string
{
    case HTTP = 'http';

    case WebSocket = 'ws';

    case Quic = 'quic';

    case gRPC = 'grpc';

    case HTTPUpgrade = 'httpupgrade';
}
