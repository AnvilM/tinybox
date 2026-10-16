<?php

declare(strict_types=1);

namespace App\Domain\Shared\Ports\Http;

use App\Domain\Shared\Exception\HTTP\HttpException;
use Psl\HTTP\Message\Response;

interface HttpPort
{
    
    /**
     * Sends http GET request
     *
     * @param float $timeout Timeout in seconds
     * @param string $url Url to send request
     * @param array<string, string> $headers Request headers
     *
     * @return Response Response
     *
     * @throws HttpException If unable to send request e.g. invalid url, or timeout
     */
    public function get(float $timeout, string $url, array $headers = []): Response;
}