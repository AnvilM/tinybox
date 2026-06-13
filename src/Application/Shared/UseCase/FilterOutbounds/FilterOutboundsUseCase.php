<?php

declare(strict_types=1);

namespace App\Application\Shared\UseCase\FilterOutbounds;

use App\Application\Shared\DTO\UseCase\FilterOutbounds\FilterOutboundsDTO;
use App\Domain\Outbound\Collection\OutboundMap;
use App\Domain\Outbound\Collection\UniqueOutboundsMap;
use App\Domain\Outbound\Specification\OutboundCountryCodeSpecification;
use App\Domain\Outbound\Specification\OutboundExcludeCountryCodeSpecification;
use App\Domain\Outbound\Specification\OutboundExcludeTagSpecification;
use App\Domain\Outbound\Specification\OutboundExcludeTypeSpecification;
use App\Domain\Outbound\Specification\OutboundTypeSpecification;
use App\Domain\Outbound\VO\OutboundTypeVO;
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
    public function handle(FilterOutboundsDTO $DTO): UniqueOutboundsMap
    {
        $ignoreOutbounds = $DTO->ignoreOutbounds ? $DTO->outboundsMap->withTags($DTO->ignoreOutbounds) : new UniqueOutboundsMap();

        /**
         * Create exclude country code specification
         */
        $excludeCountrySpecification = $DTO->filterExcludeCountryCodesDTO
            ? $this->createExcludeCountrySpecification(
                $DTO->outboundsMap, $DTO->filterExcludeCountryCodesDTO->excludeCountryCodes,
                $DTO->filterExcludeCountryCodesDTO->outboundIpFallback,
                $DTO->filterExcludeCountryCodesDTO->onlyAvailable
            ) : null;


        /**
         * Create exclude tag specification
         */
        $excludeTagSpecification = $DTO->excludeOutbounds ? new OutboundExcludeTagSpecification($DTO->excludeOutbounds) : null;


        /**
         * Create exclude outbound type specification
         */
        $excludeOutboundTypeSpecification = $DTO->filterExcludeOutboundTypes ? new OutboundExcludeTypeSpecification(
            OutboundTypeVO::fromStringValues($DTO->filterExcludeOutboundTypes)
        ) : null;


        /**
         * Create outbound type specification
         */
        $outboundTypeSpecification = $DTO->filterOutboundTypes ? new OutboundTypeSpecification(
            OutboundTypeVO::fromStringValues($DTO->filterOutboundTypes)
        ) : null;


        /**
         * Create country code specification
         */
        $countryCodeSpecification = $DTO->filterCountryCodesDTO
            ? $this->createCountryCodeSpecification(
                $DTO->outboundsMap, $DTO->filterCountryCodesDTO->countryCodes,
                $DTO->filterCountryCodesDTO->outboundIpFallback,
                $DTO->filterCountryCodesDTO->onlyAvailable
            ) : null;


        /**
         * Apply specifications
         */
        $outbounds = $DTO->outboundsMap->filter(new Vector([
            $excludeTagSpecification,
            $excludeCountrySpecification,
            $countryCodeSpecification,
            $excludeOutboundTypeSpecification,
            $outboundTypeSpecification,
        ])->filter(fn(mixed $spec) => $spec !== null));

        return $outbounds->merge($ignoreOutbounds);
    }

    /**
     * @throws CriticalException
     */
    private function createExcludeCountrySpecification(OutboundMap $outboundsMap, VectorInterface $excludeCountry, bool $outboundIpFallback, bool $onlyAvailable): OutboundExcludeCountryCodeSpecification
    {
        try {
            $outboundsCountryCodes = $this->outboundCountyCodePort->getCountryCodes($outboundsMap, $outboundIpFallback);

            return new OutboundExcludeCountryCodeSpecification(
                $outboundsCountryCodes,
                $excludeCountry,
                $onlyAvailable
            );
        } catch (CompositeException $e) {
            throw new CriticalException("Cant get outbounds ip's", $e->getMessage());
        }
    }

    /**
     * @throws CriticalException
     */
    public function createCountryCodeSpecification(OutboundMap $outboundsMap, VectorInterface $countryCodes, bool $outboundIpFallback, bool $onlyAvailable): OutboundCountryCodeSpecification
    {

        try {
            $outboundsCountryCodes = $this->outboundCountyCodePort->getCountryCodes($outboundsMap, $outboundIpFallback);


            return new OutboundCountryCodeSpecification(
                $outboundsCountryCodes,
                $countryCodes,
                $onlyAvailable
            );
        } catch (CompositeException $e) {
            throw new CriticalException("Cant get outbounds ip's", $e->getMessage());
        }
    }
}