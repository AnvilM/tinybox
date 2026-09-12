<?php

declare(strict_types=1);

namespace App\Application\Outbound\Filter\Criteria;

use App\Application\Outbound\Filter\Interface\OutboundFilterCriteriaInterface;
use Psl\Collection\VectorInterface;

/**
 * Exclude outbounds whose resolved country code is in the given list.
 */
final readonly class CountryCodeExcludeCriteria implements OutboundFilterCriteriaInterface
{
    /**
     * @param VectorInterface<string> $countryCodes
     * @param bool $outboundIpFallback Resolve country code by outbound's own IP if no explicit one is set
     * @param bool $onlyAvailable Whether outbounds with an unresolved country code should be dropped
     */
    public function __construct(
        public VectorInterface $countryCodes,
        public bool            $outboundIpFallback,
        public bool            $onlyAvailable,
    )
    {
    }
}
