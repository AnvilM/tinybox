<?php

declare(strict_types=1);

namespace App\Domain\Shared\VO\Outbound\Shadowsocks\Plugin;

enum ShadowsocksPlugin: string
{
    case OBFS_LOCAL = 'obfs-local';

    case V2RAY_PLUGIN = 'v2ray-plugin';
}