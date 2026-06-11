<?php

declare(strict_types=1);

namespace App\Domain\Subscription\VO\RawSubscription;

use App\Domain\Outbound\Collection\UniqueOutboundsMap;

final readonly class RawOutboundsSubscriptionVO extends RawSubscriptionVO
{
    public function __construct(
        string                    $name,
        string                    $url,
        string                    $type,
        public UniqueOutboundsMap $outbounds
    )
    {
        parent::__construct($name, $url, $type);
    }
}