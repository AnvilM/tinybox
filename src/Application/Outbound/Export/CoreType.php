<?php

declare(strict_types=1);

namespace App\Application\Outbound\Export;

/**
 * Proxy core whose config format the array is generated for.
 *
 * NOTE: adding a new core (e.g. Clash or Hysteria2) only requires a
 * change here. Mapping/constraint rules live in the exporters themselves.
 */
enum CoreType: string
{
    case SingBox = 'sing-box';
    case Xray = 'xray';
}
