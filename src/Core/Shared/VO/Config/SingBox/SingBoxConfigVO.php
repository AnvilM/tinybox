<?php

declare(strict_types=1);

namespace App\Core\Shared\VO\Config\SingBox;

use App\Core\Shared\VO\Config\SingBox\Templates\TemplatesSingBoxConfigVO;

final readonly class SingBoxConfigVO
{
    public function __construct(
        public string                   $binary,
        public TemplatesSingBoxConfigVO $templates,
    )
    {
    }
}