<?php

declare(strict_types=1);

namespace App\Domain\Scheme\Entity;

use App\Domain\Shared\VO\Outbound\OutboundTypeVO;
use App\Domain\Shared\VO\Outbound\Transport\TransportTypeVO;
use App\Domain\Shared\VO\Shared\NonEmptyStringVO;
use App\Domain\Shared\VO\Shared\PortVO;
use InvalidArgumentException;
use Psl\Hash\Algorithm;

final readonly class Scheme
{
    private OutboundTypeVO $type;
    private NonEmptyStringVO $uuid;
    private NonEmptyStringVO $server;
    private PortVO $server_port;
    private NonEmptyStringVO $sni;
    private NonEmptyStringVO $pbk;
    private NonEmptyStringVO $sid;
    private NonEmptyStringVO $tag;
    private ?NonEmptyStringVO $flow;
    private ?NonEmptyStringVO $fp;
    private ?TransportTypeVO $transportType;

    /**
     * @throws InvalidArgumentException Throws if given invalid data
     */
    public function __construct(
        OutboundTypeVO    $type,
        NonEmptyStringVO  $uuid,
        NonEmptyStringVO  $server,
        PortVO            $server_port,
        NonEmptyStringVO  $sni,
        NonEmptyStringVO  $pbk,
        NonEmptyStringVO  $sid,
        ?NonEmptyStringVO $tag,
        ?NonEmptyStringVO $flow,
        ?NonEmptyStringVO $fp,
        ?TransportTypeVO  $transportType
    )
    {
        $this->type = $type;
        $this->uuid = $uuid;
        $this->server = $server;
        $this->server_port = $server_port;
        $this->sni = $sni;
        $this->pbk = $pbk;
        $this->sid = $sid;

        $this->flow = $flow;
        $this->fp = $fp;
        $this->transportType = $transportType;
        $this->tag = $tag ?? new NonEmptyStringVO($this->generateTag());

    }

    private function generateTag(): string
    {
        $rawTag = $this->type->value;
        $rawTag .= $this->uuid->getValue();
        $rawTag .= $this->server->getValue();
        $rawTag .= $this->server_port->getPort();
        $rawTag .= $this->sni->getValue();
        $rawTag .= $this->pbk->getValue();
        $rawTag .= $this->sid->getValue();
        $rawTag .= $this->flow?->getValue();
        $rawTag .= $this->fp?->getValue();

        return \Psl\Hash\hash($rawTag, Algorithm::Murmur3F);
    }

    public function equals(Scheme $scheme): bool
    {
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
            $this->getTransportType() === $scheme->getTransportType()
        );
    }

    public function getType(): OutboundTypeVO
    {
        return $this->type;
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

    public function getSid(): string
    {
        return $this->sid->getValue();
    }

    public function getFlow(): ?string
    {
        return $this->flow?->getValue();
    }

    public function getFp(): ?string
    {
        return $this->fp?->getValue();
    }

    public function getTransportType(): TransportTypeVO
    {
        return $this->transportType;
    }

    public function getTag(): string
    {
        return $this->tag->getValue();
    }

    public function getHash(): string
    {
        return \Psl\Hash\hash($this->toRawScheme(), Algorithm::Murmur3F);
    }

    public function toRawScheme(): string
    {
        $rawScheme = $this->type->value . "://";
        $rawScheme .= $this->uuid->getValue() . "@";
        $rawScheme .= $this->server->getValue() . ":";
        $rawScheme .= $this->server_port->getPort() . "?";
        $rawScheme .= "sni=" . $this->sni->getValue();
        $rawScheme .= "&pbk=" . $this->pbk->getValue();
        $rawScheme .= "&sid=" . $this->sid->getValue();

        if ($this->flow) $rawScheme .= "&flow=" . $this->flow->getValue();
        if ($this->fp) $rawScheme .= "&fp=" . $this->fp->getValue();
        if ($this->getTransportType()) $rawScheme .= "&type=" . $this->getTransportType()->value;

        $rawScheme .= "#" . $this->tag->getValue();

        return $rawScheme;
    }

}