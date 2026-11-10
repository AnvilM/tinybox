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
            default => throw new UnsupportedTransportException($rawOutbound->transportType)
        };
    }


    /**
     * @throws InvalidArgumentException
     */
    private function createWebSocketTransport(RawOutboundVO $rawOutbound): WebSocketTransportVO
    {
        /**
         * "host" is optional for WebSocket
         */
        $host = $rawOutbound->host !== null && $rawOutbound->host !== ''
            ? new NonEmptyStringVO($rawOutbound->host)
            : null;

        return new WebSocketTransportVO(
            new NonEmptyStringVO($rawOutbound->path),
            $host,
        );
    }


    /**
     * @throws InvalidArgumentException
     */
    private function createXHTTPTransport(RawOutboundVO $rawOutbound): XHTTPTransportVO
    {

        /**
         * "mode", "host", "path" and "extra" are optional for XHTTP
         */
        $extra = [];

        if ($rawOutbound->extra !== null && $rawOutbound->extra !== '') {
            try {
                $decoded = json_decode($rawOutbound->extra, true, 512, JSON_THROW_ON_ERROR);
            } catch (JsonException) {
                throw new InvalidArgumentException('Invalid XHTTP extra');
            }

            if (is_array($decoded)) $extra = $decoded;
        }

        return new XHTTPTransportVO(
            $this->optionalString($rawOutbound->mode),
            $this->optionalString($rawOutbound->host),
            $this->optionalString($rawOutbound->path),
            $extra
        );
    }

    /**
     * @throws InvalidArgumentException
     */
    private function optionalString(?string $value): ?NonEmptyStringVO
    {
        return $value === null || $value === '' ? null : new NonEmptyStringVO($value);
    }
}