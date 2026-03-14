<?php

declare(strict_types=1);

namespace App\Domain\Scheme\Entity;

use App\Domain\Scheme\Exception\UnsupportedSchemeType;
use App\Domain\Shared\VO\Shared\OutboundTypeVO;
use InvalidArgumentException;
use Psl\Hash\Algorithm;
use ValueError;

final readonly class Scheme
{
    private OutboundTypeVO $type;
    private string $uuid;
    private string $server;
    private int $server_port;
    private string $sni;
    private string $pbk;
    private string $sid;
    private string $tag;
    private ?string $flow;
    private ?string $fp;

    /**
     * @throws InvalidArgumentException Throws if given invalid data
     * @throws UnsupportedSchemeType
     */
    public function __construct(
        ?string $type,
        ?string $tag,
        ?string $uuid,
        ?string $server,
        ?int    $server_port,
        ?string $sni,
        ?string $pbk,
        ?string $sid,
        ?string $flow,
        ?string $fp
    )
    {
        $this->type = $this->assertTypeVO($type);
        $this->uuid = $this->assertNonEmptyString($uuid, 'uuid');
        $this->server = $this->assertNonEmptyString($server, 'server');
        $this->server_port = $this->assertPositiveInt($server_port, 'server_port');
        $this->sni = $this->assertNonEmptyString($sni, 'sni');
        $this->pbk = $this->assertNonEmptyString($pbk, 'pbk');
        $this->sid = $this->assertNonEmptyString($sid, 'sid');
        $this->flow = $this->assertNullableString($flow);
        $this->fp = $this->assertNullableString($fp);

        if ($tag === null) $tag = $this->generateTag();
        $this->tag = $this->assertNonEmptyString($tag, 'tag');

    }

    /**
     * @throws UnsupportedSchemeType
     */
    private function assertTypeVO(string $value): OutboundTypeVO
    {
        try {
            return OutboundTypeVO::from($value);
        } catch (ValueError) {
            throw new UnsupportedSchemeType($value);
        }
    }

    private function assertNonEmptyString(?string $value, string $field): string
    {
        if (!$value || trim($value) === '') {
            throw new InvalidArgumentException("$field is required");
        }

        return $value;
    }

    private function assertPositiveInt(?int $value, string $field): int
    {
        if (!$value || $value <= 0) {
            throw new InvalidArgumentException("$field is invalid");
        }
        return $value;
    }

    private function assertNullableString(?string $value): ?string
    {
        if ($value === null || trim($value) === '') return null;


        return trim($value);
    }

    private function generateTag(): string
    {
        $rawTag = $this->type->value;
        $rawTag .= $this->uuid;
        $rawTag .= $this->server;
        $rawTag .= $this->server_port;
        $rawTag .= $this->sni;
        $rawTag .= $this->pbk;
        $rawTag .= $this->sid;
        $rawTag .= $this->flow;
        $rawTag .= $this->fp;

        return \Psl\Hash\hash($rawTag, Algorithm::Murmur3F);
    }

    public function equals(Scheme $scheme): bool
    {
        return (
            $this->type === $scheme->getType() &&
            $this->uuid === $scheme->getUuid() &&
            $this->server === $scheme->getServer() &&
            $this->server_port === $scheme->getServerPort() &&
            $this->sni === $scheme->getSni() &&
            $this->pbk === $scheme->getPbk() &&
            $this->sid === $scheme->getSid() &&
            $this->flow === $scheme->getFlow() &&
            $this->getFp() === $scheme->getFp()
        );
    }

    public function getType(): OutboundTypeVO
    {
        return $this->type;
    }

    public function getUuid(): string
    {
        return $this->uuid;
    }

    public function getServer(): string
    {
        return $this->server;
    }

    public function getServerPort(): int
    {
        return $this->server_port;
    }

    public function getSni(): string
    {
        return $this->sni;
    }

    public function getPbk(): string
    {
        return $this->pbk;
    }

    public function getSid(): string
    {
        return $this->sid;
    }

    public function getFlow(): ?string
    {
        return $this->flow;
    }

    public function getFp(): ?string
    {
        return $this->fp;
    }

    public function getTag(): string
    {
        return $this->tag;
    }

    public function getHash(): string
    {
        return \Psl\Hash\hash($this->toRawScheme(), Algorithm::Murmur3F);
    }

    public function toRawScheme(): string
    {
        $rawScheme = $this->type->value . "://";
        $rawScheme .= $this->uuid . "@";
        $rawScheme .= $this->server . ":";
        $rawScheme .= $this->server_port . "?";
        $rawScheme .= "sni=" . $this->sni;
        $rawScheme .= "&pbk=" . $this->pbk;
        $rawScheme .= "&sid=" . $this->sid;

        if ($this->flow) $rawScheme .= "&flow=" . $this->flow;
        if ($this->fp) $rawScheme .= "&fp=" . $this->fp;

        $rawScheme .= "#" . $this->tag;

        return $rawScheme;
    }
}