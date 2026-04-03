<?php

declare(strict_types=1);

namespace App\Application\Shared\UseCase\FilterOutbounds;

use App\Application\Shared\DTO\UseCase\FilterOutbounds\FilterOutboundsDTO;
use App\Domain\Outbound\Collection\OutboundMap;
use App\Domain\Outbound\Specification\OutboundCountryCodeSpecification;
use App\Domain\Outbound\Specification\OutboundTagSpecification;
use App\Domain\Shared\Exception\CriticalException;
use App\Domain\Shared\Ports\OutboundTest\OutboundCountyCode\OutboundCountyCodePort;
use Psl\Async\Exception\CompositeException;
use Psl\Collection\Vector;
use Psl\Collection\VectorInterface;

final readonly class FilterOutboundsUseCase
{

    public function __construct(
        private OutboundCountyCodePort $outboundCountyCodePort,
    )
    {
    }

    /**
     * @throws CriticalException
     */
    public function handle(FilterOutboundsDTO $DTO): OutboundMap
    {
        $excludeCountrySpecification = $DTO->filterCountryCodesDTO
            ? $this->createExcludeCountrySpecification(
                $DTO->outboundsMap, $DTO->filterCountryCodesDTO->excludeCountryCodes,
                $DTO->filterCountryCodesDTO->force, $DTO->filterCountryCodesDTO->exceptOutbounds
            ) : null;


        $excludeTagSpecification = $DTO->excludeTags ?
            $this->createExcludeTagSpecification(
                $DTO->excludeTags
            ) : null;

        return $DTO->outboundsMap->filter(new Vector([
            $excludeTagSpecification,
            $excludeCountrySpecification,
        ])->filter(fn(mixed $spec) => $spec !== null));
    }

    /**
     * @throws CriticalException
     */
    private function createExcludeCountrySpecification(OutboundMap $outboundsMap, VectorInterface $excludeCountry, bool $force, ?VectorInterface $exceptOutbounds): OutboundCountryCodeSpecification
    {
        /**
         * Try to create outbound to exclude country except
         */
        try {
            $outboundsCountryCodes = $this->outboundCountyCodePort->getCountryCodes($outboundsMap, $force);


            return new OutboundCountryCodeSpecification(
                $outboundsCountryCodes,
                $excludeCountry,
                $exceptOutbounds,
            );
        } catch (CompositeException $e) {
            throw new CriticalException("Cant get outbounds ip's", $e->getMessage());
        }
    }


    private function createExcludeTagSpecification(VectorInterface $tags): OutboundTagSpecification
    {
        return new OutboundTagSpecification($tags);
    }
}