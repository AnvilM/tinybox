<?php

declare(strict_types=1);

namespace App\Application\Shared\UseCase\FilterOutbounds;

use App\Application\Shared\DTO\UseCase\FilterOutbounds\FilterOutboundsDTO;
use App\Domain\Outbound\Collection\OutboundMap;
use App\Domain\Outbound\Specification\OutboundCountryCodeSpecification;
use App\Domain\Outbound\Specification\OutboundExcludeCountryCodeSpecification;
use App\Domain\Outbound\Specification\OutboundExcludeTagSpecification;
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
        /**
         * Create exclude country code specification
         */
        $excludeCountrySpecification = $DTO->filterExcludeCountryCodesDTO
            ? $this->createExcludeCountrySpecification(
                $DTO->outboundsMap, $DTO->filterExcludeCountryCodesDTO->excludeCountryCodes,
                $DTO->filterExcludeCountryCodesDTO->outboundIpFallback,
                $DTO->filterExcludeCountryCodesDTO->exceptOutbounds,
                $DTO->filterExcludeCountryCodesDTO->onlyAvailable
            ) : null;


        /**
         * Create exclude tag specification
         */
        $excludeTagSpecification = $DTO->excludeTags ?
            $this->createExcludeTagSpecification(
                $DTO->excludeTags
            ) : null;

        /**
         * Create country code specification
         */
        $countryCodeSpecification = $DTO->filterCountryCodesDTO
            ? $this->createCountryCodeSpecification(
                $DTO->outboundsMap, $DTO->filterCountryCodesDTO->countryCodes,
                $DTO->filterCountryCodesDTO->outboundIpFallback,
                $DTO->filterCountryCodesDTO->exceptOutbounds,
                $DTO->filterCountryCodesDTO->onlyAvailable
            ) : null;


        /**
         * Apply specifications
         */
        return $DTO->outboundsMap->filter(new Vector([
            $excludeTagSpecification,
            $excludeCountrySpecification,
            $countryCodeSpecification,
        ])->filter(fn(mixed $spec) => $spec !== null));
    }

    /**
     * @throws CriticalException
     */
    private function createExcludeCountrySpecification(OutboundMap $outboundsMap, VectorInterface $excludeCountry, bool $outboundIpFallback, ?VectorInterface $exceptOutbounds, bool $onlyAvailable): OutboundExcludeCountryCodeSpecification
    {
        try {
            $outboundsCountryCodes = $this->outboundCountyCodePort->getCountryCodes($outboundsMap, $outboundIpFallback);

            return new OutboundExcludeCountryCodeSpecification(
                $outboundsCountryCodes,
                $excludeCountry,
                $exceptOutbounds,
                $onlyAvailable
            );
        } catch (CompositeException $e) {
            throw new CriticalException("Cant get outbounds ip's", $e->getMessage());
        }
    }


    private function createExcludeTagSpecification(VectorInterface $tags): OutboundExcludeTagSpecification
    {
        return new OutboundExcludeTagSpecification($tags);
    }

    /**
     * @throws CriticalException
     */
    public function createCountryCodeSpecification(OutboundMap $outboundsMap, VectorInterface $countryCodes, bool $outboundIpFallback, ?VectorInterface $exceptOutbounds, bool $onlyAvailable): OutboundCountryCodeSpecification
    {

        try {
            $outboundsCountryCodes = $this->outboundCountyCodePort->getCountryCodes($outboundsMap, $outboundIpFallback);


            return new OutboundCountryCodeSpecification(
                $outboundsCountryCodes,
                $countryCodes,
                $exceptOutbounds,
                $onlyAvailable
            );
        } catch (CompositeException $e) {
            throw new CriticalException("Cant get outbounds ip's", $e->getMessage());
        }
    }
}