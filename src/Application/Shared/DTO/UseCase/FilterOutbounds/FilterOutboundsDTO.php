<?php

declare(strict_types=1);

namespace App\Application\Shared\DTO\UseCase\FilterOutbounds;

use App\Domain\Outbound\Collection\OutboundMap;
use Psl\Collection\VectorInterface;

final class FilterOutboundsDTO
{
    /**
     * @param OutboundMap $outboundsMap Outbounds map to filter
     * @param VectorInterface<string>|null $excludeTags Exclude outbounds with provided tags
     * @param FilterExcludeCountryCodesDTO<string>|null $filterExcludeCountryCodesDTO Exclude outbounds with ips in provided countries
     * @param null|FilterCountryCodesDTO<string> $filterCountryCodesDTO Exclude outbounds with ips not in provided countries
     */
    public function __construct(
        public readonly OutboundMap          $outboundsMap,
        public ?VectorInterface              $excludeTags = null,
        public ?FilterExcludeCountryCodesDTO $filterExcludeCountryCodesDTO = null,
        public ?FilterCountryCodesDTO        $filterCountryCodesDTO = null,

    )
    {
    }

    public function setFilterExcludeCountryCodesDTO(FilterExcludeCountryCodesDTO $filterCountryCodesDTO): void
    {
        $this->filterExcludeCountryCodesDTO = $filterCountryCodesDTO;
    }

    public function setFilterCountryCodesDTO(FilterCountryCodesDTO $filterCountryCodesDTO): void
    {
        $this->filterCountryCodesDTO = $filterCountryCodesDTO;
    }
}