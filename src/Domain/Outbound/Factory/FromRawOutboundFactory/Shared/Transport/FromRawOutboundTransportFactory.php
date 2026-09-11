<?php

declare(strict_types=1);

namespace App\Domain\Outbound\Factory\FromRawOutboundFactory\Shared\Transport;

use App\Domain\Outbound\Exception\UnsupportedTransportException;
use App\Domain\Outbound\VO\RawOutboundVO;
use App\Domain\Outbound\VO\Transport\TransportTypeVO;
use App\Domain\Outbound\VO\Transport\TransportVO;
use App\Domain\Outbound\VO\Transport\WebSocketTransportVO;
use App\Domain\Shared\VO\Shared\NonEmptyStringVO;
use InvalidArgumentException;

final readonly class FromRawOutboundTransportFactory
{
    /**
     * @throws InvalidArgumentException
     * @throws UnsupportedTransportException
     */
    public function create(RawOutboundVO $rawOutbound): TransportVO
    {
        return match (TransportTypeVO::tryFrom($rawOutbound->transportType)) {
            TransportTypeVO::WebSocket => $this->createWebSocketTransport($rawOutbound),
            default => throw new UnsupportedTransportException()
        };
    }


    /**
     * @throws InvalidArgumentException
     */
    private function createWebSocketTransport(RawOutboundVO $rawOutbound): WebSocketTransportVO
    {
        return new WebSocketTransportVO(
            new NonEmptyStringVO($rawOutbound->path),
            new NonEmptyStringVO($rawOutbound->host),
        );
    }
}