<?php

declare(strict_types=1);

namespace App\Core\Entity;

final readonly class Scheme
{
    public string $type;
    public string $uuid;
    public string $server;
    public int $port;

    // Required query params
    public string $sni;
    public string $pbk;
    public string $sid;

    // Optional query params
    public ?string $flow;
    public ?string $fp;

    /**
     * @throws InvalidArgumentException
     */
    public function __construct(string $rawSchemeString)
    {
        $parts = parse_url($rawSchemeString);

        if ($parts === false) {
            throw new InvalidArgumentException('Invalid scheme format');
        }

        $this->type = $this->requireNonEmpty($parts['scheme'] ?? null, 'Missing type in scheme');
        $this->uuid = $this->requireNonEmpty($parts['pass'] ?? null, 'Missing uuid in scheme');
        $this->server = $this->requireNonEmpty($parts['host'] ?? null, 'Missing server in scheme');

        if (!isset($parts['port']) || !is_int($parts['port']) || $parts['port'] <= 0 || $parts['port'] > 65535) {
            throw new InvalidArgumentException('Invalid or missing port in scheme');
        }
        $this->port = $parts['port'];

        $query = [];
        if (!empty($parts['query'])) {
            parse_str($parts['query'], $query);
        }

        // Required params
        $this->sni = $this->requireQueryParam($query, 'sni');
        $this->pbk = $this->requireQueryParam($query, 'pbk');
        $this->sid = $this->requireQueryParam($query, 'sid');

        // Optional params
        $this->flow = $this->optionalQueryParam($query, 'flow');
        $this->fp = $this->optionalQueryParam($query, 'fp');
    }

    private function requireNonEmpty(?string $value, string $errorMessage): string
    {
        if ($value === null || trim($value) === '') {
            throw new InvalidArgumentException($errorMessage);
        }

        return $value;
    }

    /**
     * @param array<string, mixed> $query
     */
    private function requireQueryParam(array $query, string $key): string
    {
        if (!array_key_exists($key, $query)) {
            throw new InvalidArgumentException("Missing required query parameter: {$key}");
        }

        $value = $query[$key];

        if (!is_string($value) || trim($value) === '') {
            throw new InvalidArgumentException("Invalid value for query parameter: {$key}");
        }

        return $value;
    }

    /**
     * @param array<string, mixed> $query
     */
    private function optionalQueryParam(array $query, string $key): ?string
    {
        if (!array_key_exists($key, $query)) {
            return null;
        }

        $value = $query[$key];

        if (!is_string($value) || trim($value) === '') {
            return null;
        }

        return $value;
    }
}