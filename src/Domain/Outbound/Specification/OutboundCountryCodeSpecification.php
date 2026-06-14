<?php

declare(strict_types=1);

namespace App\Domain\Outbound\Specification;

use App\Domain\Interface\Outbound\OutboundSpecificationInterface;
use App\Domain\Outbound\Entity\Outbound;
use Psl\Collection\MutableMap;
use Psl\Collection\VectorInterface;

final readonly class OutboundCountryCodeSpecification implements OutboundSpecificationInterface
{
    /**
     * @param MutableMap<string, string> $outboundsCountryCode
     * @param VectorInterface<string> $countryCodes
     * @param bool $onlyAvailable
     */
    public function __construct(
        private MutableMap      $outboundsCountryCode,
        private VectorInterface $countryCodes,
        private bool            $onlyAvailable
    )
    {
    }

    public function isSatisfiedBy(Outbound $outbound): bool
    {
        foreach ($this->outboundsCountryCode as $outboundTag => $outboundCountryCode) {
            if ($outboundTag === $outbound->getTagString()) {
                foreach ($this->countryCodes as $countryCode) {
                    if ($countryCode === $outboundCountryCode) return true;
                }
            }
        }

        return !$this->onlyAvailable;
    }
}