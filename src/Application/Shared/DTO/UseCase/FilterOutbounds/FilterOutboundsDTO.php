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
     * @param FilterCountryCodesDTO<string>|null $filterCountryCodesDTO Exclude outbounds with ips in provided countries
     */
    public function __construct(
        public readonly OutboundMap   $outboundsMap,
        public ?VectorInterface       $excludeTags = null,
        public ?FilterCountryCodesDTO $filterCountryCodesDTO = null
    )
    {
    }

    public function setFilterCountryCodesDTO(FilterCountryCodesDTO $filterCountryCodesDTO): void
    {
        $this->filterCountryCodesDTO = $filterCountryCodesDTO;
    }
}