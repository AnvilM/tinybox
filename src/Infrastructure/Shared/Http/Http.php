<?php

declare(strict_types=1);

namespace App\Infrastructure\Shared\Http;

use App\Domain\Shared\Exception\HTTP\HttpException;
use App\Domain\Shared\Ports\Http\HttpPort;
use Psl\Async\TimeoutCancellationToken;
use Psl\DateTime\Duration;
use Psl\HTTP\Client\Client;
use Psl\HTTP\Message\FieldMap;
use Psl\HTTP\Message\Request;
use Psl\HTTP\Message\Response;
use Throwable;
use function Psl\URL\parse;

final class Http implements HttpPort
{
    private Client $client;

    public function __construct()
    {
        $this->client = new Client();
    }

    public function get(float $timeout, string $url, array $headers = []): Response
    {
        $fields = [];
        foreach ($headers as $name => $value) {
            $fields[] = [$name, $value];
        }

        $request = new Request(
            'GET',
            parse($url),
            headers: FieldMap::from($fields),
        );

        try {
            return $this->client->send(
                $request,
                cancellation: new TimeoutCancellationToken(
                    Duration::seconds((int)$timeout),
                ),
            )->response;
        } catch (Throwable $e) {
            throw new HttpException(previous: $e);
        }
    }
}