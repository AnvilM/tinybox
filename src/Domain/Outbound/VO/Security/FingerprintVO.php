<?php

declare(strict_types=1);

namespace App\Domain\Outbound\VO\Security;

enum FingerprintVO: string
{
    case Chrome = 'chrome';
    case Firefox = 'firefox';
    case Safari = 'safari';
    case IOS = 'ios';
    case Android = 'android';
    case Edge = 'edge';
    case _360 = '360';
    case qq = 'qq';
    case Random = 'random';
    case Randomized = 'randomized';
    

}
