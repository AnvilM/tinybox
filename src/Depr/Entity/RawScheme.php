<?php

declare(strict_types=1);

namespace App\Core\Entity;

use InvalidArgumentException;

final readonly class RawScheme
{
    public string $type;
    public string $uuid;
    public string $server;
    public int $port;
    /** @var array<string, string>|null $queryParams  */
    public ?array $queryParams;
    public ?string $tag;

    /**
     * @param string $rawSchemeString
     * @throws InvalidArgumentException
     */
    public function __construct(string $rawSchemeString)
    {
        $parts = parse_url($rawSchemeString);

        if ($parts === false) {
            throw new InvalidArgumentException("Invalid scheme format");
        }

        // Scheme
        if (empty($parts['scheme'])) {
            throw new InvalidArgumentException("Missing type in scheme");
        }
        $this->type = $parts['scheme'];

        // UUID
        if (empty($parts['pass'])) {
            throw new InvalidArgumentException("Missing uuid in scheme");
        }
        $this->uuid = $parts['pass'];

        // Host
        if (empty($parts['host'])) {
            throw new InvalidArgumentException("Missing server in scheme");
        }
        $this->server = $parts['host'];

        // Port
        if (empty($parts['port'])) {
            throw new InvalidArgumentException("Missing port in scheme");
        }
        $this->port = (int)$parts['port'];
        

        // Query parameters
        if (!empty($parts['query'])) {
            parse_str($parts['query'], $this->queryParams);
        } else {
            $this->queryParams = null;
        }

        // Fragment/tag
        $this->tag = $parts['fragment'] ?? null;
    }
}