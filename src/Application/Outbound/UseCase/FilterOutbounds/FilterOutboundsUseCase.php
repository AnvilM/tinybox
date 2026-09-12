<?php

declare(strict_types=1);

namespace App\Application\Outbound\UseCase\FilterOutbounds;

use App\Application\Outbound\DTO\UseCase\FilterOutbounds\FilterOutboundsDTO;
use App\Application\Outbound\Filter\OutboundFilterService;
use App\Domain\Outbound\Collection\OutboundMap;
use App\Domain\Shared\Exception\CriticalException;

final readonly class FilterOutboundsUseCase
{
    public function __construct(
        private OutboundFilterService $filterService,
    )
    {
    }

    /**
     * @throws CriticalException
     */
    public function handle(FilterOutboundsDTO $DTO): OutboundMap
    {
        $ignoredOutbounds = $DTO->ignoreOutbounds
            ? $DTO->outboundsMap->withTags($DTO->ignoreOutbounds)
            : new OutboundMap();

        $filteredOutbounds = $this->filterService->filter($DTO->outboundsMap, $DTO->criteria);

        return $filteredOutbounds->merge($ignoredOutbounds);
    }
}
