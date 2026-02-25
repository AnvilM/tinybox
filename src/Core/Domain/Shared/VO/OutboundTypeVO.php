<?php

declare(strict_types=1);

namespace App\Core\Domain\Shared\VO;

enum OutboundTypeVO: string
{
    case Vless = 'vless';
}