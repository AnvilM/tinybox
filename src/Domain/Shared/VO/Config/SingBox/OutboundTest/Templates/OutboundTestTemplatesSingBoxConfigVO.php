<?php

declare(strict_types=1);

namespace App\Domain\Shared\VO\Config\SingBox\OutboundTest\Templates;

final readonly class OutboundTestTemplatesSingBoxConfigVO
{
    public function __construct(
        public string $outbound,
        public string $config,
    )
    {
    }
}