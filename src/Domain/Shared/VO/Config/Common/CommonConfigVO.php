<?php

declare(strict_types=1);

namespace App\Domain\Shared\VO\Config\Common;

use App\Domain\Shared\VO\Config\Common\DNS\DNSCommonConfigVO;

final readonly class CommonConfigVO
{
    public function __construct(
        public DNSCommonConfigVO $dnsCommonConfig,
    )
    {
    }
}