<?php

declare(strict_types=1);

namespace App\Core\Domain\Scheme\Entity;

use App\Core\Domain\Shared\VO\OutboundTypeVO;
use InvalidArgumentException;
use ValueError;

final readonly class Scheme
{
    public OutboundTypeVO $type;
    public string $tag;
    public string $uuid;
    public string $server;
    public int $server_port;
    public string $sni;
    public string $pbk;
    public string $sid;
    public ?string $flow;
    public ?string $fp;

    /**
     * @throws InvalidArgumentException Throws if given invalid data
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
        $this->tag = $this->assertNonEmptyString($tag, 'tag');
        $this->uuid = $this->assertNonEmptyString($uuid, 'uuid');
        $this->server = $this->assertNonEmptyString($server, 'server');
        $this->server_port = $this->assertPositiveInt($server_port, 'server_port');
        $this->sni = $this->assertNonEmptyString($sni, 'sni');
        $this->pbk = $this->assertNonEmptyString($pbk, 'pbk');
        $this->sid = $this->assertNonEmptyString($sid, 'sid');
        $this->flow = $this->assertNullableString($flow);
        $this->fp = $this->assertNullableString($fp);
    }

    private function assertTypeVO(mixed $value): OutboundTypeVO
    {
        try {
            return OutboundTypeVO::from($value);
        } catch (ValueError) {
            throw new InvalidArgumentException("Unsupported type: $value");
        }
    }

    private function assertNonEmptyString(?string $value, string $field): string
    {
        if (!$value || trim($value) === '') {
            throw new InvalidArgumentException("$field is required");
        }

        return $value;
    }

    private function assertPositiveInt(int $value, string $field): int
    {
        if (!$value || $value <= 0) {
            throw new InvalidArgumentException("$field is invalid");
        }
        return $value;
    }

    private function assertNullableString(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        return trim($value);
    }
}