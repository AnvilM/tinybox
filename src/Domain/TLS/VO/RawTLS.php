<?php

declare(strict_types=1);

namespace App\Domain\TLS\VO;

final readonly class RawTLS
{
    public function __construct(
        public ?string     $serverName,
        public ?RawReality $reality,
        public ?RawUTLS    $utls,
        public bool        $enabled
    )
    {
    }

}