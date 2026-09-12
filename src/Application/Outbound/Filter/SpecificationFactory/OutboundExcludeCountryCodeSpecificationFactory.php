<?php

declare(strict_types=1);

namespace App\Application\Outbound\Filter\SpecificationFactory;

use App\Application\Outbound\Exception\Filter\UnsupportedOutboundFilterCriteriaException;
use App\Application\Outbound\Filter\Criteria\CountryCodeExcludeCriteria;
use App\Application\Outbound\Filter\Interface\OutboundFilterCriteriaInterface;
use App\Application\Outbound\Filter\Interface\OutboundSpecificationFactoryInterface;
use App\Application\Outbound\Filter\Specification\OutboundExcludeCountryCodeSpecification;
use App\Domain\Interface\Outbound\OutboundSpecificationInterface;
use App\Domain\Outbound\Collection\OutboundMap;
use App\Domain\Shared\Exception\CriticalException;
use App\Domain\Shared\Ports\OutboundTest\OutboundCountyCode\OutboundCountyCodePort;
use Psl\Async\Exception\CompositeException;

final readonly class OutboundExcludeCountryCodeSpecificationFactory implements OutboundSpecificationFactoryInterface
{
    public function __construct(
        private OutboundCountyCodePort $outboundCountyCodePort,
    )
    {
    }

    public function supports(OutboundFilterCriteriaInterface $criteria): bool
    {
        return $criteria instanceof CountryCodeExcludeCriteria;
    }

    /**
     * @throws CriticalException
     */
    public function create(OutboundFilterCriteriaInterface $criteria, OutboundMap $outboundsMap): OutboundSpecificationInterface
    {
        if (!$criteria instanceof CountryCodeExcludeCriteria) {
            throw new UnsupportedOutboundFilterCriteriaException($criteria);
        }

        try {
            $outboundsCountryCodes = $this->outboundCountyCodePort->getCountryCodes($outboundsMap, $criteria->outboundIpFallback);
        } catch (CompositeException $e) {
            throw new CriticalException("Cant get outbounds ip's", $e->getMessage());
        }

        return new OutboundExcludeCountryCodeSpecification(
            $outboundsCountryCodes,
            $criteria->countryCodes,
            $criteria->onlyAvailable,
        );
    }
}
