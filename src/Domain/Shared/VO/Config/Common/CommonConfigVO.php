<?php

declare(strict_types=1);

namespace App\Domain\Shared\VO\Config\Common;

use App\Domain\Shared\VO\Config\Common\DNS\DNSCommonConfigVO;
use App\Domain\Shared\VO\Config\Common\Http\HttpCommonConfigVO;

final readonly class CommonConfigVO
{
    public function __construct(
        public DNSCommonConfigVO  $dnsCommonConfig,
        public HttpCommonConfigVO $httpCommonConfig,
    )
    {
    }
}