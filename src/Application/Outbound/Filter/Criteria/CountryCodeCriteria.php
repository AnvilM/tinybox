<?php

declare(strict_types=1);

namespace App\Application\Outbound\Filter\Criteria;

use App\Application\Outbound\Filter\Contract\OutboundFilterCriteriaExceptOutboundsInterface;
use Psl\Collection\VectorInterface;

/**
 * Keep only outbounds whose resolved country code is in the given list.
 */
final readonly class CountryCodeCriteria implements OutboundFilterCriteriaExceptOutboundsInterface
{
    /**
     * @param VectorInterface<string> $countryCodes
     * @param bool $outboundIpFallback Resolve country code by outbound's own IP if no explicit one is set
     * @param bool $onlyAvailable Whether outbounds with an unresolved country code should be dropped.
     *                            NOTE: independent from {@see CountryCodeExcludeCriteria::$onlyAvailable} -
     *                            each of the two criteria carries its own flag, one never affects the other.
     * @param VectorInterface<string>|null $exceptOutbounds Tags of outbounds that always satisfy THIS
     *                                                       criteria regardless of their country code.
     *                                                       They remain fully subject to every other criteria.
     */
    public function __construct(
        public VectorInterface  $countryCodes,
        public bool             $outboundIpFallback,
        public bool             $onlyAvailable,
        public ?VectorInterface $exceptOutbounds = null,
    )
    {
    }

    public function getExceptOutbounds(): ?VectorInterface
    {
        return $this->exceptOutbounds;
    }
}
