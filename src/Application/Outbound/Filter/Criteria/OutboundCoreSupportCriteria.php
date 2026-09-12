<?php

declare(strict_types=1);

namespace App\Application\Outbound\Filter\Criteria;

use App\Application\Outbound\Export\CoreType;
use App\Application\Outbound\Filter\Contract\OutboundFilterCriteriaInterface;

final readonly class OutboundCoreSupportCriteria implements OutboundFilterCriteriaInterface
{
    public function __construct(
        public CoreType $coreType,
    )
    {
    }
}