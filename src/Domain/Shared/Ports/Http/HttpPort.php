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
     * Redirects following and Accept-Encoding header are controlled by the common.http config
     *
     * @param float $timeout Timeout in seconds
     * @param string $url Url to send request
     * @param array<string, string> $headers Request headers
     *
     * @return Response Response. Gzip encoded body is returned decoded
     *
     * @throws HttpException If unable to send request e.g. invalid url, timeout or too many redirects
     */
    public function get(float $timeout, string $url, array $headers = []): Response;
}