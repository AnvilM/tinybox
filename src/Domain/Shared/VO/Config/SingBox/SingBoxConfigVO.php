<?php

declare(strict_types=1);

namespace App\Domain\Shared\VO\Config\SingBox;

use App\Domain\Shared\VO\Config\SingBox\OutboundTest\OutboundTestSingBoxConfigVO;
use App\Domain\Shared\VO\Config\SingBox\Templates\TemplatesSingBoxConfigVO;

final readonly class SingBoxConfigVO
{
    public function __construct(
        public string                      $binary,
        public TemplatesSingBoxConfigVO    $templates,
        public string                      $defaultConfigPath,
        public string                      $systemdServiceName,
        public OutboundTestSingBoxConfigVO $outboundTest
    )
    {
    }
}