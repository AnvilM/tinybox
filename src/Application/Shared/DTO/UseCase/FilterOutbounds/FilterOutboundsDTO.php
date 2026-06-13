<?php

declare(strict_types=1);

namespace App\Application\Shared\DTO\UseCase\FilterOutbounds;

use App\Domain\Outbound\Collection\UniqueOutboundsMap;
use Psl\Collection\VectorInterface;

final readonly class FilterOutboundsDTO
{
    /**
     * @param UniqueOutboundsMap $outboundsMap Outbounds map to filter
     * @param VectorInterface<string>|null $excludeOutbounds Exclude outbounds with provided tags
     * @param FilterExcludeCountryCodesDTO<string>|null $filterExcludeCountryCodesDTO Exclude outbounds with ips in provided countries
     * @param null|FilterCountryCodesDTO<string> $filterCountryCodesDTO Exclude outbounds with ips not in provided countries
     */
    public function __construct(
        public UniqueOutboundsMap            $outboundsMap,
        public ?VectorInterface              $ignoreOutbounds = null,
        public ?VectorInterface              $excludeOutbounds = null,
        public ?FilterExcludeCountryCodesDTO $filterExcludeCountryCodesDTO = null,
        public ?FilterCountryCodesDTO        $filterCountryCodesDTO = null,
        public ?VectorInterface              $filterExcludeOutboundTypes = null,
        public ?VectorInterface              $filterOutboundTypes = null,

    )
    {
    }
}