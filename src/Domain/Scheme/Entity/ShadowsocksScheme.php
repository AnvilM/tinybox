<?php

declare(strict_types=1);

namespace App\Domain\Scheme\Entity;

use App\Domain\Scheme\VO\SchemeTypeVO;
use App\Domain\Scheme\VO\ShadowsocksScheme\Plugin\ShadowsocksPlugin;
use App\Domain\Scheme\VO\ShadowsocksScheme\Plugin\ShadowsocksPluginVO;
use App\Domain\Scheme\VO\ShadowsocksScheme\Userinfo\ShadowsocksMethod;
use App\Domain\Scheme\VO\ShadowsocksScheme\Userinfo\ShadowsocksUserinfoVO;
use App\Domain\Shared\VO\Shared\NonEmptyStringVO;
use App\Domain\Shared\VO\Shared\PortVO;
use Psl\Hash\Algorithm;

final readonly class ShadowsocksScheme extends Scheme
{
    private ShadowsocksUserinfoVO $userinfo;
    private ?ShadowsocksPluginVO $plugin;
    private NonEmptyStringVO $server;
    private PortVO $serverPort;

    public function __construct(?NonEmptyStringVO $tag, ShadowsocksUserinfoVO $userinfo, ?ShadowsocksPluginVO $plugin, NonEmptyStringVO $server, PortVO $serverPort)
    {
        $this->userinfo = $userinfo;
        $this->plugin = $plugin;
        $this->server = $server;
        $this->serverPort = $serverPort;

        parent::__construct($tag);
    }

    public function equals(Scheme $scheme): bool
    {
        if (!($scheme instanceof self)) return false;

        return (
            $this->getType() === $scheme->getType() &&
            $this->getPlugin() === $scheme->getPlugin() &&
            $this->getPluginOptions() === $scheme->getPluginOptions() &&
            $this->getMethod() === $scheme->getMethod() &&
            $this->getPassword() === $scheme->getPassword() &&
            $this->getServer() === $scheme->getServer() &&
            $this->getServerPort() === $scheme->getServerPort()
        );
    }

    public function getType(): SchemeTypeVO
    {
        return SchemeTypeVO::SS;
    }

    public function getPlugin(): ?ShadowsocksPlugin
    {
        return $this->plugin?->getPlugin();
    }

    public function getPluginOptions(): ?string
    {
        return $this->plugin?->getPluginOptions();
    }

    public function getMethod(): ShadowsocksMethod
    {
        return $this->userinfo->getMethod();
    }

    public function getPassword(): string
    {
        return $this->userinfo->getPassword();
    }

    public function getServer(): string
    {
        return $this->server->getValue();
    }

    public function getServerPort(): int
    {
        return $this->serverPort->getPort();
    }

    public function toRawScheme(): string
    {
        $rawScheme = $this->getType()->value . "://";
        $rawScheme .= $this->userinfo->getRawUserinfo() . "@";
        $rawScheme .= $this->getServer() . ":";
        $rawScheme .= $this->getServerPort();

        if ($this->plugin !== null) $rawScheme .= "?plugin=" . $this->plugin->getRawPlugin();

        $rawScheme .= "#" . $this->getTag();

        return $rawScheme;
    }

    protected function generateTag(): string
    {
        $rawTag = $this->getType()->value;
        $rawTag .= $this->getMethod()->value;
        $rawTag .= $this->getPassword();
        $rawTag .= $this->getServer();
        $rawTag .= $this->getServerPort();
        $rawTag .= $this->getPlugin()?->value;
        $rawTag .= $this->getPluginOptions();

        return \Psl\Hash\hash($rawTag, Algorithm::Murmur3F);
    }
}