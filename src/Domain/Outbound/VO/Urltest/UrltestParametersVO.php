<?php

declare(strict_types=1);

namespace App\Domain\Outbound\VO\Urltest;

use App\Domain\Interface\Shared\Equable;

/**
 * Urltest parameters read from the common templates once, while the urltest is created.
 *
 * NOTE: 'outbounds' (sing-box), 'selector' (balancer) and 'subjectSelector' (observatory) stay inside the arrays
 * as they are: these are static template values, merged with the members tags while creating the config.
 */
final readonly class UrltestParametersVO implements Equable
{
    /**
     * @param array<string, mixed> $singBox Sing-box urltest outbound template without the 'tag' field
     * @param array<string, mixed> $xrayBalancer Xray balancer template without the 'tag' field
     * @param array<string, mixed> $xrayObservatory Xray observatory template (as is)
     */
    public function __construct(
        private array $singBox = [],
        private array $xrayBalancer = [],
        private array $xrayObservatory = [],
    )
    {
    }

    /**
     * @return array<string, mixed>
     */
    public function getSingBox(): array
    {
        return $this->singBox;
    }

    /**
     * @return array<string, mixed>
     */
    public function getXrayBalancer(): array
    {
        return $this->xrayBalancer;
    }

    /**
     * @return array<string, mixed>
     */
    public function getXrayObservatory(): array
    {
        return $this->xrayObservatory;
    }

    /**
     * @inheritdoc
     */
    public function equals(mixed $other): bool
    {
        return $other instanceof static &&
            $this->singBox == $other->singBox &&
            $this->xrayBalancer == $other->xrayBalancer &&
            $this->xrayObservatory == $other->xrayObservatory;
    }
}
