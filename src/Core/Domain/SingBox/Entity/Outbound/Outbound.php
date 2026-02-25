<?php

declare(strict_types=1);

namespace App\Core\Domain\SingBox\Entity\Outbound;

use App\Core\Domain\Shared\VO\OutboundTypeVO;
use App\Core\Domain\SingBox\Entity\TLS\TLS;
use InvalidArgumentException;

final readonly class Outbound
{
    private OutboundTypeVO $type;
    private string $tag;
    private string $server;
    private int $serverPort;
    private string $uuid;
    private ?string $flow;
    private ?TLS $tls;

    public function __construct(
        OutboundTypeVO $type,
        string         $tag,
        string         $server,
        int            $serverPort,
        string         $uuid,
        ?string        $flow = null,
        ?TLS           $tls = null
    )
    {
        $this->type = $type;
        $this->tag = $this->assertNonEmptyString($tag, 'tag');
        $this->server = $this->assertNonEmptyString($server, 'server');
        $this->serverPort = $this->assertPositiveInt($serverPort, 'server_port');
        $this->uuid = $this->assertNonEmptyString($uuid, 'uuid');
        $this->flow = $this->assertNullableString($flow);
        $this->tls = $tls;
    }

    private function assertNonEmptyString(string $value, string $name): string
    {

        if (trim($value) === '') throw new InvalidArgumentException("$name is required");

        return trim($value);
    }

    private function assertPositiveInt(int $value, string $name): int
    {
        if ($value <= 0) throw new InvalidArgumentException("$name is invalid");

        return $value;
    }

    private function assertNullableString(?string $value): ?string
    {
        if (trim($value) === '') return null;

        return trim($value);
    }

    public function toArray(): array
    {
        return array_filter([
            'type' => $this->type->value,
            'tag' => $this->tag,
            'server' => $this->server,
            'server_port' => $this->serverPort,
            'uuid' => $this->uuid,
            'flow' => $this->flow,
            'tls' => $this->tls?->toArray(),
        ], static fn($value) => $value !== null);
    }

    public function getTag(): string
    {
        return $this->tag;
    }
}