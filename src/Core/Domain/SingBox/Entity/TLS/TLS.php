<?php

declare(strict_types=1);

namespace App\Core\Domain\SingBox\Entity\TLS;

use InvalidArgumentException;

final readonly class TLS
{
    private string $serverName;
    private ?Reality $reality;
    private ?UTLS $utls;
    private bool $enabled;

    public function __construct(
        string   $serverName,
        ?Reality $reality = null,
        ?UTLS    $utls = null,
        bool     $enabled = true
    )
    {
        $this->serverName = $this->assertNonEmptyString($serverName, 'server_name');
        $this->reality = $reality;
        $this->utls = $utls;
        $this->enabled = $enabled;
    }

    private function assertNonEmptyString(string $value, string $name): string
    {

        if (trim($value) === '') throw new InvalidArgumentException("$name is required");

        return trim($value);
    }

    public function toArray(): array
    {
        return array_filter([
            'enabled' => $this->enabled,
            'server_name' => $this->serverName,
            'reality' => $this->reality?->toArray(),
            'utls' => $this->utls?->toArray(),
        ], static fn($value) => $value !== null);
    }
}