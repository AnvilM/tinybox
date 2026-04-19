<?php

declare(strict_types=1);

namespace App\Domain\Outbound\Entity;

use App\Domain\Interface\Subscription\DetourProvider;
use App\Domain\Outbound\Entity\TLS\TLS;
use App\Domain\Outbound\VO\OutboundTypeVO;
use App\Domain\Shared\VO\Shared\NonEmptyStringVO;
use App\Domain\Shared\VO\Shared\PortVO;
use Override;

final readonly class VlessOutbound extends Outbound implements DetourProvider
{
    private NonEmptyStringVO $server;
    private PortVO $serverPort;
    private NonEmptyStringVO $uuid;
    private ?NonEmptyStringVO $flow;
    private ?TLS $tls;
    private ?NonEmptyStringVO $detourTag;


    public function __construct(
        NonEmptyStringVO  $tag,
        int               $id,
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

        parent::__construct($tag, $id);
    }

    /**
     * @inheritdoc
     */
    public function setDetour(Outbound $detour): void
    {
        $this->detourTag = $detour->getTag();
    }

    /**
     * @inheritdoc
     */
    public function equals(mixed $other): bool
    {
        return $other instanceof self &&
            $this->server->equals($other->server) &&
            $this->serverPort->equals($other->serverPort) &&
            $this->uuid->equals($other->uuid) &&
            $this->equalsNullable($this->flow, $other->flow) &&
            $this->equalsNullable($this->tls, $other->tls) &&
            $this->equalsNullable($this->detourTag ?? null, $other->detourTag ?? null);
    }

    #[Override]
    public function toArray(): array
    {
        return array_filter([
            'type' => $this->getType()->value,
            'tag' => $this->getTagString(),
            'server' => $this->server->getValue(),
            'server_port' => $this->serverPort->getPort(),
            'uuid' => $this->uuid->getValue(),
            'flow' => $this->flow?->getValue(),
            'tls' => $this->tls?->toArray(),
            'detour' => isset($this->detourTag) ? $this->detourTag->getValue() : null,
        ], static fn($value) => $value !== null);
    }

    #[Override]
    public function getType(): OutboundTypeVO
    {
        return OutboundTypeVO::Vless;
    }

    #[Override]
    public function getServer(): ?string
    {
        return $this->server->getValue();
    }

    #[Override]
    public function getServerPort(): ?int
    {
        return $this->serverPort->getPort();
    }


}