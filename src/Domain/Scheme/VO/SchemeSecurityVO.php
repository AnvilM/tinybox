<?php

declare(strict_types=1);

namespace App\Domain\Scheme\VO;

enum SchemeSecurityVO: string
{
    case Reality = 'reality';

    case TLS = 'tls';
}
