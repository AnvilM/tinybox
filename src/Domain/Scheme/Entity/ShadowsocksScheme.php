<?php

declare(strict_types=1);

namespace App\Domain\Scheme\Entity;

use App\Domain\Scheme\VO\SchemeTypeVO;
use App\Domain\Shared\Trait\ComparesNullable;
use App\Domain\Shared\VO\Outbound\Shadowsocks\Plugin\ShadowsocksPlugin;
use App\Domain\Shared\VO\Outbound\Shadowsocks\Plugin\ShadowsocksPluginVO;
use App\Domain\Shared\VO\Outbound\Shadowsocks\Userinfo\ShadowsocksMethod;
use App\Domain\Shared\VO\Outbound\Shadowsocks\Userinfo\ShadowsocksUserinfoVO;
use App\Domain\Shared\VO\Outbound\Transport\TransportTypeVO;
use App\Domain\Shared\VO\Shared\NonEmptyStringVO;
use App\Domain\Shared\VO\Shared\PortVO;
use Psl\Hash\Algorithm;

final readonly class ShadowsocksScheme extends Scheme
{
    use ComparesNullable;

    private ShadowsocksUserinfoVO $userinfo;
    private ?ShadowsocksPluginVO $plugin;
    private NonEmptyStringVO $server;
    private PortVO $serverPort;
    private ?TransportTypeVO $transport;
    private ?NonEmptyStringVO $path;
    private ?NonEmptyStringVO $host;

    public function __construct(
        ?NonEmptyStringVO     $tag,
        ShadowsocksUserinfoVO $userinfo,
        ?ShadowsocksPluginVO  $plugin,
        NonEmptyStringVO      $server,
        PortVO                $serverPort,
        ?TransportTypeVO      $transport,
        ?NonEmptyStringVO     $path,
        ?NonEmptyStringVO     $host
    )
    {
        $this->userinfo = $userinfo;
        $this->plugin = $plugin;
        $this->server = $server;
        $this->serverPort = $serverPort;
        $this->transport = $transport;
        $this->path = $path;
        $this->host = $host;

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
            $this->getServerPort() === $scheme->getServerPort() &&
            $this->getTransport() === $scheme->getTransport() &&
            $this->getPath() === $scheme->getPath() &&
            $this->getHost() === $scheme->getHost()
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

    public function getTransport(): ?TransportTypeVO
    {
        return $this->transport;
    }

    public function getPath(): ?string
    {
        return $this->path->getValue();
    }

    public function getHost(): ?string
    {
        return $this->host->getValue();
    }

    public function getUserinfo(): ShadowsocksUserinfoVO
    {
        return $this->userinfo;
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