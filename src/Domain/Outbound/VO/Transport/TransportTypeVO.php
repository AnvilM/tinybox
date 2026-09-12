<?php

declare(strict_types=1);

namespace App\Domain\Outbound\VO\Transport;

enum TransportTypeVO: string
{
    case HTTP = 'http';

    case WebSocket = 'ws';

    case Quic = 'quic';

    case gRPC = 'grpc';

    case HTTPUpgrade = 'httpupgrade';

    case XHTTP = 'xhttp';
}
