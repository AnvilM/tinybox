<?php

declare(strict_types=1);

namespace App\Application\Outbound\Filter\SpecificationFactory;

use App\Application\Outbound\Exception\Filter\UnsupportedOutboundFilterCriteriaException;
use App\Application\Outbound\Filter\Contract\OutboundFilterCriteriaInterface;
use App\Application\Outbound\Filter\Contract\OutboundSpecificationFactoryInterface;
use App\Application\Outbound\Filter\Criteria\TypeExcludeCriteria;
use App\Application\Outbound\Filter\Specification\OutboundExcludeTypeSpecification;
use App\Domain\Interface\Outbound\OutboundSpecificationInterface;
use App\Domain\Outbound\Collection\OutboundMap;
use App\Domain\Outbound\VO\ProtocolVO;

final readonly class OutboundExcludeTypeSpecificationFactory implements OutboundSpecificationFactoryInterface
{
    public function supports(OutboundFilterCriteriaInterface $criteria): bool
    {
        return $criteria instanceof TypeExcludeCriteria;
    }

    public function create(OutboundFilterCriteriaInterface $criteria, OutboundMap $outboundsMap): OutboundSpecificationInterface
    {
        if (!$criteria instanceof TypeExcludeCriteria) {
            throw new UnsupportedOutboundFilterCriteriaException($criteria);
        }

        return new OutboundExcludeTypeSpecification(ProtocolVO::fromStringValues($criteria->types));
    }
}
