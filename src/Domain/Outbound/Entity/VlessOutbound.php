<?php

declare(strict_types=1);

namespace App\Domain\Outbound\Entity;

use App\Domain\Outbound\Entity\TLS\TLS;
use App\Domain\Outbound\VO\OutboundTypeVO;
use App\Domain\Shared\VO\Shared\NonEmptyStringVO;
use App\Domain\Shared\VO\Shared\PortVO;
use Override;

final readonly class VlessOutbound extends Outbound
{
    private NonEmptyStringVO $server;
    private PortVO $serverPort;
    private NonEmptyStringVO $uuid;
    private ?NonEmptyStringVO $flow;
    private ?TLS $tls;


    public function __construct(
        NonEmptyStringVO  $tag,
        NonEmptyStringVO  $server,
        PortVO            $serverPort,
        NonEmptyStringVO  $uuid,
        ?NonEmptyStringVO $flow,
        ?TLS              $tls
    )
    {
        $this->server = $server;
        $this->serverPort = $serverPort;
        $this->uuid = $uuid;
        $this->flow = $flow;
        $this->tls = $tls;

        parent::__construct($tag);
    }


    #[Override]
    public function toArray(): array
    {
        return array_filter([
            'type' => $this->getType()->value,
            'tag' => $this->getTag(),
            'server' => $this->server->getValue(),
            'server_port' => $this->serverPort->getPort(),
            'uuid' => $this->uuid->getValue(),
            'flow' => $this->flow?->getValue(),
            'tls' => $this->tls?->toArray(),
        ], static fn($value) => $value !== null);
    }


    #[Override]
    public function getType(): OutboundTypeVO
    {
        return OutboundTypeVO::Vless;
    }
}