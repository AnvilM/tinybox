<?php

declare(strict_types=1);

namespace App\Domain\Outbound\Entity;

use App\Domain\Interface\Subscription\DetourProvider;
use App\Domain\Outbound\VO\OutboundTypeVO;
use App\Domain\Shared\VO\Shared\NonEmptyStringVO;
use App\Domain\Shared\VO\Shared\PortVO;
use Override;

final readonly class ShadowsocksOutbound extends Outbound implements DetourProvider
{
    private NonEmptyStringVO $server;
    private PortVO $serverPort;
    private NonEmptyStringVO $method;
    private NonEmptyStringVO $password;
    private ?NonEmptyStringVO $plugin;
    private ?NonEmptyStringVO $pluginOptions;
    private ?NonEmptyStringVO $detourTag;


    public function __construct(
        NonEmptyStringVO  $tag,
        int               $id,
        NonEmptyStringVO  $server,
        PortVO            $serverPort,
        NonEmptyStringVO  $method,
        NonEmptyStringVO  $password,
        ?NonEmptyStringVO $plugin,
        ?NonEmptyStringVO $pluginOptions
    )
    {
        $this->server = $server;
        $this->serverPort = $serverPort;
        $this->method = $method;
        $this->password = $password;
        $this->plugin = $plugin;
        $this->pluginOptions = $pluginOptions;


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
            $this->method->equals($other->method) &&
            $this->password->equals($other->password) &&
            $this->equalsNullable($this->plugin, $other->plugin) &&
            $this->equalsNullable($this->pluginOptions, $other->pluginOptions) &&
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
            'method' => $this->method->getValue(),
            'password' => $this->password->getValue(),
            'plugin' => $this->plugin?->getValue(),
            'plugin_opts' => $this->pluginOptions?->getValue(),
            'detour' => isset($this->detourTag) ? $this->detourTag->getValue() : null,
        ], static fn($value) => $value !== null);
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
}