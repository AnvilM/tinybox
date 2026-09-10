<?php

declare(strict_types=1);

namespace App\Domain\Outbound\Factory\FromRawOutboundFactory\Shared\Transport;

use App\Domain\Outbound\DTO\RawOutboundDTO;
use App\Domain\Outbound\Exception\UnsupportedTransportException;
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
    public static function create(RawOutboundDTO $rawOutbound): TransportVO
    {
        return match (TransportTypeVO::tryFrom($rawOutbound->transportType)) {
            TransportTypeVO::WebSocket => self::createWebSocketTransport($rawOutbound),
            default => throw new UnsupportedTransportException()
        };
    }


    /**
     * @throws InvalidArgumentException
     */
    private static function createWebSocketTransport(RawOutboundDTO $rawOutbound): WebSocketTransportVO
    {
        return new WebSocketTransportVO(
            new NonEmptyStringVO($rawOutbound->path),
            new NonEmptyStringVO($rawOutbound->host),
        );
    }
}