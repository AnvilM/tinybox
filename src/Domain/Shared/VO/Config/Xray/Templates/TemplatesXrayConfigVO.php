<?php

declare(strict_types=1);

namespace App\Domain\Shared\VO\Config\Xray\Templates;

final readonly class TemplatesXrayConfigVO
{
    public function __construct(
        public string $outbound,
        public string $observatory,
        public string $balancer,
        public string $config,
    )
    {
    }
}