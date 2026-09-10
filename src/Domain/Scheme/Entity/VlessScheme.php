<?php

declare(strict_types=1);

namespace App\Domain\Scheme\Entity;

use App\Domain\Scheme\VO\SchemeSecurityVO;
use App\Domain\Scheme\VO\SchemeTypeVO;
use App\Domain\Shared\VO\Outbound\Transport\TransportTypeVO;
use App\Domain\Shared\VO\Shared\NonEmptyStringVO;
use App\Domain\Shared\VO\Shared\PortVO;
use Override;
use Psl\Hash\Algorithm;

final readonly class VlessScheme extends Scheme
{
    private NonEmptyStringVO $uuid;
    private NonEmptyStringVO $server;
    private PortVO $server_port;
    private NonEmptyStringVO $sni;
    private NonEmptyStringVO $pbk;
    private ?NonEmptyStringVO $sid;
    private ?NonEmptyStringVO $flow;
    private ?NonEmptyStringVO $fp;
    private ?TransportTypeVO $transportType;
    private ?SchemeSecurityVO $security;
    private ?NonEmptyStringVO $path;
    private ?NonEmptyStringVO $host;


    public function __construct(
        NonEmptyStringVO  $uuid,
        NonEmptyStringVO  $server,
        PortVO            $server_port,
        NonEmptyStringVO  $sni,
        NonEmptyStringVO  $pbk,
        ?NonEmptyStringVO $sid,
        ?NonEmptyStringVO $tag,
        ?NonEmptyStringVO $flow,
        ?NonEmptyStringVO $fp,
        ?TransportTypeVO  $transportType,
        ?SchemeSecurityVO $security,
        ?NonEmptyStringVO $path,
        ?NonEmptyStringVO $host
    )
    {
        $this->uuid = $uuid;
        $this->server = $server;
        $this->server_port = $server_port;
        $this->sni = $sni;
        $this->pbk = $pbk;
        $this->sid = $sid;

        $this->flow = $flow;
        $this->fp = $fp;
        $this->transportType = $transportType;
        $this->security = $security;
        $this->path = $path;
        $this->host = $host;

        parent::__construct($tag);
    }

    #[Override]
    public function equals(Scheme $scheme): bool
    {
        if (!($scheme instanceof self)) return false;

        return (
            $this->getType() === $scheme->getType() &&
            $this->getUuid() === $scheme->getUuid() &&
            $this->getServer() === $scheme->getServer() &&
            $this->getServerPort() === $scheme->getServerPort() &&
            $this->getSni() === $scheme->getSni() &&
            $this->getPbk() === $scheme->getPbk() &&
            $this->getSid() === $scheme->getSid() &&
            $this->getFlow() === $scheme->getFlow() &&
            $this->getFp() === $scheme->getFp() &&
            $this->getTransportType() === $scheme->getTransportType() &&
            $this->getSecurity() === $scheme->getSecurity() &&
            $this->getPath() === $scheme->getPath() &&
            $this->getHost() === $scheme->getHost()
        );
    }

    #[Override]
    public function getType(): SchemeTypeVO
    {
        return SchemeTypeVO::Vless;
    }


    public function getUuid(): string
    {
        return $this->uuid->getValue();
    }

    public function getServer(): string
    {
        return $this->server->getValue();
    }

    public function getServerPort(): int
    {
        return $this->server_port->getPort();
    }

    public function getSni(): string
    {
        return $this->sni->getValue();
    }

    public function getPbk(): string
    {
        return $this->pbk->getValue();
    }

    public function getSid(): ?string
    {
        return $this->sid?->getValue();
    }

    public function getFlow(): ?string
    {
        return $this->flow?->getValue();
    }

    public function getFp(): ?string
    {
        return $this->fp?->getValue();
    }

    public function getTransportType(): ?TransportTypeVO
    {
        return $this->transportType;
    }

    public function getSecurity(): ?SchemeSecurityVO
    {
        return $this->security;
    }

    public function getPath(): ?string
    {
        return $this->path->getValue();
    }

    public function getHost(): ?string
    {
        return $this->host->getValue();
    }

    #[Override]
    protected function generateTag(): string
    {
        $rawTag = $this->getType()->value;
        $rawTag .= $this->uuid->getValue();
        $rawTag .= $this->server->getValue();
        $rawTag .= $this->server_port->getPort();
        $rawTag .= $this->sni->getValue();
        $rawTag .= $this->pbk->getValue();
        $rawTag .= $this->getSid();
        $rawTag .= $this->flow?->getValue();
        $rawTag .= $this->fp?->getValue();

        return \Psl\Hash\hash($rawTag, Algorithm::Murmur3F);
    }


}