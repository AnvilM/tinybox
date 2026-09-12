<?php

declare(strict_types=1);

namespace App\Domain\Shared\VO\Config\Xray;

use App\Domain\Shared\VO\Config\Xray\Templates\TemplatesXrayConfigVO;

final readonly class XrayConfigVO
{
    public function __construct(
        public TemplatesXrayConfigVO $templates
    )
    {
    }
}