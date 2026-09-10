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
        public ?string $fp,
        public ?string $transportType,
        public ?string $shadowsocksPlugin,
        public ?string $security,
        public ?string $path,
        public ?string $host
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
        $string .= "transportType: $this->transportType\n";
        $string .= "shadowsocksPlugin: $this->shadowsocksPlugin\n";
        $string .= "security: $this->security\n";
        $string .= "path: $this->path\n";
        $string .= "host: $this->host\n";

        return $string;

    }


    public function format(): string
    {
        $string = $this->type . '://';
        $string .= $this->uuid . '@';
        $string .= $this->server . ':';
        $string .= $this->server_port . '?';
        if ($this->flow) $string .= 'flow=' . $this->flow . '&';
        if ($this->type) $string .= 'type=' . $this->transportType . '&';
        if ($this->security) $string .= 'security=' . $this->security . '&';
        if ($this->sni) $string .= 'sni=' . $this->sni . '&';
        if ($this->fp) $string .= 'fp=' . $this->fp . '&';
        if ($this->host) $string .= 'host=' . $this->host . '&';
        if ($this->pbk) $string .= 'pbk=' . $this->pbk . '&';
        if ($this->sid) $string .= 'sid=' . $this->sid . '&';
        if ($this->path) $string .= 'path=' . $this->path . '&';
        if ($this->shadowsocksPlugin) $string .= 'plugin=' . $this->shadowsocksPlugin . '&';
        if ($this->tag) $string .= "#" . $this->tag;

        return $string;

    }
}