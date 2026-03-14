<?php

declare(strict_types=1);

namespace App\Domain\Shared\VO\Shared;

enum OutboundTypeVO: string
{
    case Vless = 'vless';
}