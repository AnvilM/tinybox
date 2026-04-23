<?php

declare(strict_types=1);

namespace App\Domain\Outbound\VO\RawOutbound;

use App\Domain\TLS\VO\RawTLS;

final readonly class RawVlessOutboundVO extends RawOutboundVO
{
    public function __construct(
        string         $type,
        string         $tag,
        public string  $server,
        public int     $serverPort,
        public string  $uuid,
        public ?string $flow,
        public ?RawTLS $tls
    )
    {
        parent::__construct($type, $tag);
    }
}