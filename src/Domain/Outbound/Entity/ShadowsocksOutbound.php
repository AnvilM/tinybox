<?php

declare(strict_types=1);

namespace App\Domain\Outbound\Entity;

use App\Domain\Interface\Subscription\DetourProvider;
use App\Domain\Shared\VO\Outbound\OutboundTypeVO;
use App\Domain\Shared\VO\Outbound\Shadowsocks\Plugin\ShadowsocksPluginVO;
use App\Domain\Shared\VO\Outbound\Shadowsocks\Userinfo\ShadowsocksUserinfoVO;
use App\Domain\Shared\VO\Outbound\Transport\TransportVO;
use App\Domain\Shared\VO\Shared\NonEmptyStringVO;
use App\Domain\Shared\VO\Shared\PortVO;
use Override;

final readonly class ShadowsocksOutbound extends Outbound implements DetourProvider
{
    private NonEmptyStringVO $server;
    private PortVO $serverPort;
    private ShadowsocksUserinfoVO $userinfo;
    private ?ShadowsocksPluginVO $plugin;
    private ?TransportVO $transport;
    private ?NonEmptyStringVO $detourTag;


    public function __construct(
        NonEmptyStringVO      $tag,
        NonEmptyStringVO      $server,
        PortVO                $serverPort,
        ShadowsocksUserinfoVO $userinfo,
        ?ShadowsocksPluginVO  $plugin,
        ?TransportVO          $transport,
    )
    {
        $this->server = $server;
        $this->serverPort = $serverPort;
        $this->userinfo = $userinfo;
        $this->plugin = $plugin;
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
            $this->userinfo->getMethod() === $other->userinfo->getMethod() &&
            $this->userinfo->getPassword() === $other->userinfo->getPassword() &&
            $this->plugin->getPlugin() === $other->plugin->getPlugin() &&
            $this->plugin->getPluginOptions() === $other->plugin->getPluginOptions() &&
            $this->equalsNullable($this->detourTag ?? null, $other->detourTag ?? null) &&
            $this->equalsNullable($this->transport, $other->transport);
    }

    public function getPlugin(): ?ShadowsocksPluginVO
    {
        return $this->plugin;
    }

    #[Override]
    public function getType(): OutboundTypeVO
    {
        return OutboundTypeVO::Shadowsocks;
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

    public function getUserinfo(): ShadowsocksUserinfoVO
    {
        return $this->userinfo;
    }

    public function getTransport(): ?TransportVO
    {
        return $this->transport;
    }
}