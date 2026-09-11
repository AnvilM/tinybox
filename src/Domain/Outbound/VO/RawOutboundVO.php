<?php

declare(strict_types=1);

namespace App\Domain\Outbound\VO;

final readonly class RawOutboundVO
{
    public function __construct(
        public ?string $protocol,
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
        public ?string $host,
        public ?string $spx
    )
    {
    }
}