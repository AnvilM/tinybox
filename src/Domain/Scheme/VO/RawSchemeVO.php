<?php

declare(strict_types=1);

namespace App\Domain\Scheme\VO;

final readonly class RawSchemeVO
{
    public function __construct(
        public ?string $type,
        public ?string $tag,
        public ?string $uuid,
        public ?string $server,
        public ?int    $server_port,
        public ?string $sni,
        public ?string $pbk,
        public ?string $sid,
        public ?string $flow,
        public ?string $fp
    )
    {
    }

    public function __toString(): string
    {
        $string = "type: $this->type\n";
        $string .= "tag: $this->tag\n";
        $string .= "uuid: $this->uuid\n";
        $string .= "server: $this->server\n";
        $string .= "server_port: $this->server_port\n";
        $string .= "sni: $this->sni\n";
        $string .= "pbk: $this->pbk\n";
        $string .= "sid: $this->sid\n";
        $string .= "flow: $this->flow\n";
        $string .= "fp: $this->fp\n";

        return $string;

    }
}