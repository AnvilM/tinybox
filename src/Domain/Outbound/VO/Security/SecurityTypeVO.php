<?php

declare(strict_types=1);

namespace App\Domain\Outbound\VO\Security;

enum SecurityTypeVO: string
{
    case Reality = 'reality';
    case TLS = 'tls';
}