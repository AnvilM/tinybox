<?php

declare(strict_types=1);

namespace App\Domain\Outbound\Factory\FromRawOutboundFactory\Shared\Transport;

use App\Domain\Outbound\Exception\UnsupportedTransportException;
use App\Domain\Outbound\VO\RawOutboundVO;
use App\Domain\Outbound\VO\Transport\TransportTypeVO;
use App\Domain\Outbound\VO\Transport\TransportVO;
use App\Domain\Outbound\VO\Transport\WebSocketTransportVO;
use App\Domain\Outbound\VO\Transport\XHTTPTransportVO;
use App\Domain\Shared\VO\Shared\NonEmptyStringVO;
use InvalidArgumentException;
use JsonException;

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
            TransportTypeVO::XHTTP => $this->createXHTTPTransport($rawOutbound),
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


    /**
     * @throws InvalidArgumentException
     */
    private function createXHTTPTransport(RawOutboundVO $rawOutbound): XHTTPTransportVO
    {

        try {
            $extra = json_decode($rawOutbound->extra, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            throw new InvalidArgumentException();
        }

        return new XHTTPTransportVO(
            new NonEmptyStringVO($rawOutbound->mode),
            new NonEmptyStringVO($rawOutbound->host),
            new NonEmptyStringVO($rawOutbound->path),
            $extra
        );
    }
}