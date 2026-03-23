<?php

declare(strict_types=1);

namespace App\Domain\Shared\VO\Outbound;

enum OutboundTypeVO: string
{
    case Vless = 'vless';
}