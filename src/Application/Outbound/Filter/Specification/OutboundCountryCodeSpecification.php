<?php

declare(strict_types=1);

namespace App\Application\Outbound\Filter\Specification;

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
        $countryCode = $this->outboundsCountryCode->get($outbound->getTagString());

        /**
         * Country code could not be resolved for this outbound at all.
         */
        if ($countryCode === null) {
            return !$this->onlyAvailable;
        }

        foreach ($this->countryCodes as $countryCodeToMatch) {
            if ($countryCodeToMatch === $countryCode) return true;
        }

        /**
         * Country code IS known, it just doesn't match the requested list -
         * this outbound must be dropped, regardless of $onlyAvailable
         * (that flag only concerns *unresolved* country codes).
         */
        return false;
    }
}
