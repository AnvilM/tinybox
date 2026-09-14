<?php

declare(strict_types=1);

namespace App\Application\Outbound\Mapper\ToSchemeString\Shared\Transport;

use App\Domain\Outbound\VO\Transport\TransportVO;
use App\Domain\Outbound\VO\Transport\WebSocketTransportVO;
use App\Domain\Outbound\VO\Transport\XHTTPTransportVO;
use InvalidArgumentException;

final readonly class ToSchemeParamsTransportMapper
{
    /**
     * @throws InvalidArgumentException
     */
    public function map(?TransportVO $transport): array
    {
        return match (true) {
            $transport instanceof WebSocketTransportVO => $this->mapWebSocketTransport($transport),
            $transport instanceof XHTTPTransportVO => $this->mapXHTTPTransport($transport),
            $transport === null => ['type' => 'tcp'],
            default => throw new InvalidArgumentException("Can't map outbound transport to scheme string: Unsupported transport type - {$transport->getType()->value}")

        };

    }


    private function mapWebSocketTransport(WebSocketTransportVO $transport): array
    {
        return [
            'type' => $transport->getType()->value,
            'path' => $transport->getPath()->getValue(),
            'host' => $transport->getHost()->getValue(),
        ];
    }

    private function mapXHTTPTransport(XHTTPTransportVO $transport): array
    {
        return [
            'type' => $transport->getType()->value,
            'mode' => $transport->getMode()->getValue(),
            'host' => $transport->getHost()->getValue(),
            'path' => $transport->getPath()->getValue(),
            'extra' => json_encode($transport->getExtra())
        ];
    }
}