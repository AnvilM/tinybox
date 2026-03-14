<?php

declare(strict_types=1);

namespace App\Infrastructure\Shared\Http;

use App\Domain\Shared\Ports\Http\HttpProt;
use Closure;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

final readonly class Http implements HttpProt
{

    public function getMultipleAsync(float $timeout, array $urls, ?Closure $fulfilled = null, ?Closure $rejected = null): void
    {
        /**
         * Create HTTP client
         */


        $client = new Client(['timeout' => 100.0]);


        /**
         * Map array of ["requestName" => "url"] to ["requestName" => Request]
         */


        $requests = array_map(function ($url) {
            return new Request('GET', $url);
        }, $urls);


        /**
         * Create pool of request and start requests
         */


        new Pool($client, $requests, [
            'concurrency' => count($urls),
            'fulfilled' => function (Response $response, $subscriptionName) use ($fulfilled) {
                if ($fulfilled !== null) $fulfilled($response->getBody()->getContents(), $subscriptionName);
            },
            'rejected' => function (Exception $exception, $name) use ($rejected) {
                if ($rejected !== null) $rejected($exception, $name);
            },
        ])->promise()->wait();
    }
}