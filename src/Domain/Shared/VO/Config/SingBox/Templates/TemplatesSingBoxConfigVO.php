<?php

declare(strict_types=1);

namespace App\Domain\Shared\VO\Config\SingBox\Templates;

final readonly class TemplatesSingBoxConfigVO
{
    public function __construct(
        public string $outbound,
        public string $outboundUrltest,
        public string $config,
    )
    {
    }
}