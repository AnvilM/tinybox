<?php

declare(strict_types=1);

namespace App\Infrastructure\Shared\Http;

use App\Domain\Shared\Exception\HTTP\HttpException;
use App\Domain\Shared\Ports\Config\ConfigInstancePort;
use App\Domain\Shared\Ports\Http\HttpPort;
use App\Infrastructure\Shared\Http\Compression\GzipDecompressor;
use Psl\Async\TimeoutCancellationToken;
use Psl\Compression\DecompressingReadHandle;
use Psl\DateTime\Duration;
use Psl\HTTP\Client\Client;
use Psl\HTTP\Client\ClientInterface;
use Psl\HTTP\Client\Exception\TooManyRedirectsException;
use Psl\HTTP\Client\RedirectClient;
use Psl\HTTP\Message\FieldMap;
use Psl\HTTP\Message\Request;
use Psl\HTTP\Message\Response;
use Throwable;
use function Psl\URL\parse;

final class Http implements HttpPort
{
    private const array GZIP_CONTENT_ENCODINGS = ['gzip', 'x-gzip'];

    private Client $client;
    private ClientInterface $redirectClient;

    public function __construct(
        private readonly ConfigInstancePort $configInstancePort,
    )
    {
        $this->client = new Client();
        $this->redirectClient = new RedirectClient($this->client);
    }

    public function get(float $timeout, string $url, array $headers = []): Response
    {
        $config = $this->configInstancePort->get()->commonConfig->httpCommonConfig;

        $fields = [];
        $hasAcceptEncoding = false;
        foreach ($headers as $name => $value) {
            $fields[] = [$name, $value];
            $hasAcceptEncoding = $hasAcceptEncoding || strtolower($name) === 'accept-encoding';
        }

        if ($config->acceptGzip && !$hasAcceptEncoding) {
            $fields[] = ['Accept-Encoding', 'gzip'];
        }

        $request = new Request(
            'GET',
            parse($url),
            headers: FieldMap::from($fields),
        );

        $client = $config->followRedirects
            ? $this->redirectClient
            : $this->client;

        try {
            $response = $client->send(
                $request,
                cancellation: new TimeoutCancellationToken(
                    Duration::seconds((int)$timeout),
                ),
            )->response;
        } catch (TooManyRedirectsException $e) {
            throw new HttpException($e->getMessage(), previous: $e);
        } catch (Throwable $e) {
            throw new HttpException(previous: $e);
        }

        return $this->decodeResponse($response);
    }

    /**
     * Decodes gzip encoded response body. Responses with any other content encoding are returned as is
     */
    private function decodeResponse(Response $response): Response
    {
        $contentEncoding = $response->headers->get('content-encoding');

        if ($response->body === null
            || $contentEncoding === null
            || !in_array(strtolower(trim($contentEncoding)), self::GZIP_CONTENT_ENCODINGS, true)
        ) {
            return $response;
        }

        return $response
            ->withBody(new DecompressingReadHandle(
                $response->body,
                new GzipDecompressor($this->client->configuration->maxResponseBodySize),
            ))
            ->withoutHeader('content-encoding')
            ->withoutHeader('content-length');
    }
}
