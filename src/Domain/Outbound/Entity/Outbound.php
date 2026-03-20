<?php

declare(strict_types=1);

namespace App\Domain\Outbound\Entity;

use App\Domain\Outbound\Entity\TLS\TLS;
use App\Domain\Shared\VO\Shared\NonEmptyStringVO;
use App\Domain\Shared\VO\Shared\OutboundTypeVO;
use App\Domain\Shared\VO\Shared\PortVO;

final readonly class Outbound
{
    private OutboundTypeVO $type;
    private NonEmptyStringVO $tag;
    private NonEmptyStringVO $server;
    private PortVO $serverPort;
    private NonEmptyStringVO $uuid;
    private ?NonEmptyStringVO $flow;
    private ?TLS $tls;


    public function __construct(
        OutboundTypeVO    $type,
        NonEmptyStringVO  $tag,
        NonEmptyStringVO  $server,
        PortVO            $serverPort,
        NonEmptyStringVO  $uuid,
        ?NonEmptyStringVO $flow,
        ?TLS              $tls
    )
    {
        $this->type = $type;
        $this->tag = $tag;
        $this->server = $server;
        $this->serverPort = $serverPort;
        $this->uuid = $uuid;
        $this->flow = $flow;
        $this->tls = $tls;
    }


    /**
     * Convert outbound entity to array
     *
     * @return array Outbound entity as array
     */
    public function toArray(): array
    {
        return array_filter([
            'type' => $this->type->value,
            'tag' => $this->tag->getValue(),
            'server' => $this->server->getValue(),
            'server_port' => $this->serverPort->getPort(),
            'uuid' => $this->uuid->getValue(),
            'flow' => $this->flow?->getValue(),
            'tls' => $this->tls?->toArray(),
        ], static fn($value) => $value !== null);
    }


    /**
     * Get outbound tag
     *
     * @return string Outbound tag
     */
    public function getTag(): string
    {
        return $this->tag->getValue();
    }
}