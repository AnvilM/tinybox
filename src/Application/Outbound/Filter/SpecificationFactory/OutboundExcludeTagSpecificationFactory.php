<?php

declare(strict_types=1);

namespace App\Application\Outbound\Filter\SpecificationFactory;

use App\Application\Outbound\Exception\Filter\UnsupportedOutboundFilterCriteriaException;
use App\Application\Outbound\Filter\Criteria\TagExcludeCriteria;
use App\Application\Outbound\Filter\Interface\OutboundFilterCriteriaInterface;
use App\Application\Outbound\Filter\Interface\OutboundSpecificationFactoryInterface;
use App\Application\Outbound\Filter\Specification\OutboundExcludeTagSpecification;
use App\Domain\Interface\Outbound\OutboundSpecificationInterface;
use App\Domain\Outbound\Collection\OutboundMap;

final readonly class OutboundExcludeTagSpecificationFactory implements OutboundSpecificationFactoryInterface
{
    public function supports(OutboundFilterCriteriaInterface $criteria): bool
    {
        return $criteria instanceof TagExcludeCriteria;
    }

    public function create(OutboundFilterCriteriaInterface $criteria, OutboundMap $outboundsMap): OutboundSpecificationInterface
    {
        if (!$criteria instanceof TagExcludeCriteria) {
            throw new UnsupportedOutboundFilterCriteriaException($criteria);
        }

        return new OutboundExcludeTagSpecification($criteria->tags);
    }
}
