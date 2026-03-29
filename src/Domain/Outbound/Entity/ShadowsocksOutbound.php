<?php

declare(strict_types=1);

namespace App\Domain\Outbound\Entity;

use App\Domain\Outbound\VO\OutboundTypeVO;
use App\Domain\Shared\VO\Shared\NonEmptyStringVO;
use App\Domain\Shared\VO\Shared\PortVO;
use Override;

final readonly class ShadowsocksOutbound extends Outbound
{
    private NonEmptyStringVO $server;
    private PortVO $serverPort;
    private NonEmptyStringVO $method;
    private NonEmptyStringVO $password;
    private ?NonEmptyStringVO $plugin;
    private ?NonEmptyStringVO $pluginOptions;


    public function __construct(NonEmptyStringVO $tag, NonEmptyStringVO $server, PortVO $serverPort, NonEmptyStringVO $method, NonEmptyStringVO $password, ?NonEmptyStringVO $plugin, ?NonEmptyStringVO $pluginOptions)
    {
        $this->server = $server;
        $this->serverPort = $serverPort;
        $this->method = $method;
        $this->password = $password;
        $this->plugin = $plugin;
        $this->pluginOptions = $pluginOptions;


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
            'method' => $this->method->getValue(),
            'password' => $this->password->getValue(),
            'plugin' => $this->plugin?->getValue(),
            'plugin_opts' => $this->pluginOptions?->getValue(),
        ], static fn($value) => $value !== null);
    }

    #[Override]
    public function getType(): OutboundTypeVO
    {
        return OutboundTypeVO::Shadowsocks;
    }
}