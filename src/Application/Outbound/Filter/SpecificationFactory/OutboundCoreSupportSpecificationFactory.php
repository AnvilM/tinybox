<?php

declare(strict_types=1);

namespace App\Application\Outbound\Filter\SpecificationFactory;

use App\Application\Outbound\Exception\Filter\UnsupportedOutboundFilterCriteriaException;
use App\Application\Outbound\Filter\Contract\OutboundFilterCriteriaInterface;
use App\Application\Outbound\Filter\Contract\OutboundSpecificationFactoryInterface;
use App\Application\Outbound\Filter\Criteria\OutboundCoreSupportCriteria;
use App\Application\Outbound\Filter\Specification\OutboundCoreSupportSpecification;
use App\Domain\Interface\Outbound\OutboundSpecificationInterface;
use App\Domain\Outbound\Collection\OutboundMap;

final readonly class OutboundCoreSupportSpecificationFactory implements OutboundSpecificationFactoryInterface
{
    public function supports(OutboundFilterCriteriaInterface $criteria): bool
    {
        return $criteria instanceof OutboundCoreSupportCriteria;
    }

    public function create(OutboundFilterCriteriaInterface $criteria, OutboundMap $outboundsMap): OutboundSpecificationInterface
    {
        if (!$criteria instanceof OutboundCoreSupportCriteria) {
            throw new UnsupportedOutboundFilterCriteriaException($criteria);
        }

        return new OutboundCoreSupportSpecification($criteria->coreType);
    }
}