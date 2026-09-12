<?php

declare(strict_types=1);

namespace App\Application\Outbound\Filter\SpecificationFactory;

use App\Application\Outbound\Exception\Filter\UnsupportedOutboundFilterCriteriaException;
use App\Application\Outbound\Filter\Contract\OutboundFilterCriteriaInterface;
use App\Application\Outbound\Filter\Contract\OutboundSpecificationFactoryInterface;
use App\Application\Outbound\Filter\Criteria\TypeCriteria;
use App\Application\Outbound\Filter\Specification\OutboundTypeSpecification;
use App\Domain\Interface\Outbound\OutboundSpecificationInterface;
use App\Domain\Outbound\Collection\OutboundMap;
use App\Domain\Outbound\VO\ProtocolVO;

final readonly class OutboundTypeSpecificationFactory implements OutboundSpecificationFactoryInterface
{
    public function supports(OutboundFilterCriteriaInterface $criteria): bool
    {
        return $criteria instanceof TypeCriteria;
    }

    public function create(OutboundFilterCriteriaInterface $criteria, OutboundMap $outboundsMap): OutboundSpecificationInterface
    {
        if (!$criteria instanceof TypeCriteria) {
            throw new UnsupportedOutboundFilterCriteriaException($criteria);
        }

        return new OutboundTypeSpecification(ProtocolVO::fromStringValues($criteria->types));
    }
}
