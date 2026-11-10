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
        $params = [
            'type' => $transport->getType()->value,
            'path' => $transport->getPath()->getValue(),
        ];

        if ($transport->getHost()) $params['host'] = $transport->getHost()->getValue();

        return $params;
    }

    private function mapXHTTPTransport(XHTTPTransportVO $transport): array
    {
        $params = ['type' => $transport->getType()->value];

        if ($transport->getMode()) $params['mode'] = $transport->getMode()->getValue();
        if ($transport->getHost()) $params['host'] = $transport->getHost()->getValue();
        if ($transport->getPath()) $params['path'] = $transport->getPath()->getValue();

        $params['extra'] = json_encode($transport->getExtra());

        return $params;
    }
}