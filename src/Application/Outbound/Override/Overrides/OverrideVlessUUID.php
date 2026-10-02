<?php

declare(strict_types=1);

namespace App\Application\Outbound\Override\Overrides;

use App\Application\Outbound\Override\Override;
use App\Application\Outbound\Override\OverrideType;
use App\Domain\Outbound\Entity\Outbound;
use App\Domain\Outbound\Entity\VlessOutbound;

final readonly class OverrideVlessUUID extends Override
{
    public function override(Outbound $outbound, OverrideType $overrideType): Outbound
    {
        if (!($outbound instanceof VlessOutbound)) return $outbound;

        return $outbound->withUUID($overrideType->getValue());
    }


}