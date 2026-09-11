<?php

declare(strict_types=1);

namespace App\Application\Outbound\Mapper\ToSchemeString\Shared\Transport;

use App\Domain\Outbound\VO\Transport\TransportVO;
use App\Domain\Outbound\VO\Transport\WebSocketTransportVO;
use InvalidArgumentException;

final readonly class ToSchemeParamsTransportMapper
{
    /**
     * @throws InvalidArgumentException
     */
    public function map(?TransportVO $transport): array
    {
        if ($transport instanceof WebSocketTransportVO) {
            return $this->mapWebSocketTransport($transport);
        }

        if ($transport === null) return [
            'type' => 'tcp'
        ];

        throw new InvalidArgumentException();
    }


    private function mapWebSocketTransport(WebSocketTransportVO $transport): array
    {
        return [
            'type' => $transport->getType()->value,
            'path' => $transport->getPath()->getValue(),
            'host' => $transport->getHost()->getValue(),
        ];
    }
}