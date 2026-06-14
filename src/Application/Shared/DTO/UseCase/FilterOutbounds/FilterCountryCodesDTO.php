<?php

declare(strict_types=1);

namespace App\Application\Shared\DTO\UseCase\FilterOutbounds;

use Psl\Collection\VectorInterface;

final readonly class FilterCountryCodesDTO
{
    /**
     * @param VectorInterface<string> $countryCodes Exclude outbounds with ips in not provided countries
     * @param bool $outboundIpFallback Check outbound ip if it unavailable instead of check real outbound ip
     */
    public function __construct(
        public VectorInterface $countryCodes,
        public bool            $outboundIpFallback = false,
        public bool            $onlyAvailable = false
    )
    {
    }
}