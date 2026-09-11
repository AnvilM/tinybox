<?php

declare(strict_types=1);

namespace App\Domain\Outbound\Entity;

use App\Domain\Interface\Subscription\DetourProvider;
use App\Domain\Outbound\VO\ProtocolVO;
use App\Domain\Outbound\VO\Security\SecurityVO;
use App\Domain\Outbound\VO\Transport\TransportVO;
use App\Domain\Shared\VO\Shared\NonEmptyStringVO;
use App\Domain\Shared\VO\Shared\PortVO;
use Override;

final readonly class VlessOutbound extends Outbound implements DetourProvider
{
    private NonEmptyStringVO $server;
    private PortVO $serverPort;
    private NonEmptyStringVO $uuid;
    private ?NonEmptyStringVO $flow;
    private ?SecurityVO $security;
    private ?TransportVO $transport;
    private ?NonEmptyStringVO $detourTag;


    public function __construct(
        ?string           $tag,
        NonEmptyStringVO  $server,
        PortVO            $serverPort,
        NonEmptyStringVO  $uuid,
        ?NonEmptyStringVO $flow,
        ?SecurityVO       $security,
        ?TransportVO      $transport,
    )
    {
        $this->server = $server;
        $this->serverPort = $serverPort;
        $this->uuid = $uuid;
        $this->flow = $flow;
        $this->security = $security;
        $this->transport = $transport;

        parent::__construct($tag);
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
    public function equalsContent(mixed $other): bool
    {
        return $other instanceof self &&
            $this->server->equals($other->server) &&
            $this->serverPort->equals($other->serverPort) &&
            $this->uuid->equals($other->uuid) &&
            $this->equalsNullable($this->flow, $other->flow) &&
            $this->equalsNullable($this->security, $other->security) &&
            $this->equalsNullable($this->detourTag ?? null, $other->detourTag ?? null) &&
            $this->equalsNullable($this->transport, $other->transport);
    }

    #[Override]
    public function getType(): ProtocolVO
    {
        return ProtocolVO::Vless;
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

    public function getSecurity(): ?SecurityVO
    {
        return $this->security;
    }

    public function getFlow(): ?string
    {
        return $this->flow?->getValue();
    }

    public function getTransport(): ?TransportVO
    {
        return $this->transport;
    }

    public function getUUID(): string
    {
        return $this->uuid->getValue();
    }
}