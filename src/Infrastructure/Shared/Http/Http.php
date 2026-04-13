<?php

declare(strict_types=1);

namespace App\Infrastructure\Shared\Http;

use App\Domain\Shared\Exception\HTTP\HttpException;
use App\Domain\Shared\Ports\Config\ConfigInstancePort;
use App\Domain\Shared\Ports\Http\HttpPort;
use Closure;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;

final readonly class Http implements HttpPort
{
    public function __construct(
        private ConfigInstancePort $configInstancePort,
    )
    {
    }

    public function getMultipleAsync(float $timeout, array $urls, ?Closure $fulfilled = null, ?Closure $rejected = null): void
    {
        /**
         * Create HTTP client
         */
        $client = new Client(['timeout' => $timeout]);


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


    public function get(float $timeout, string $url): ResponseInterface
    {
        $headers = [
            'User-Agent' => $this->configInstancePort->get()->subscriptionsConfig->useragent
        ];

        if ($this->configInstancePort->get()->subscriptionsConfig->hwid != null) {
            $headers['X-HWID'] = $this->configInstancePort->get()->subscriptionsConfig->hwid;
        }

        try {
            return new Client(['timeout' => $timeout, 'headers' => $headers])->get($url);
        } catch (GuzzleException $e) {
            throw new HttpException("Unable to send request", $e->getMessage());
        }
    }
}