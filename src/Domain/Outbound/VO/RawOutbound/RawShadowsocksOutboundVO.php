<?php

declare(strict_types=1);

namespace App\Domain\Outbound\VO\RawOutbound;

final readonly class RawShadowsocksOutboundVO extends RawOutboundVO
{
    public function __construct(
        string         $type,
        string         $tag,
        public string  $server,
        public int     $serverPort,
        public string  $method,
        public string  $password,
        public ?string $plugin,
        public ?string $pluginOptions
    )
    {
        parent::__construct($type, $tag);
    }
}