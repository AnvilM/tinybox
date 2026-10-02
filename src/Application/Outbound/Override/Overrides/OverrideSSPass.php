<?php

declare(strict_types=1);

namespace App\Application\Outbound\Override\Overrides;

use App\Application\Outbound\Override\Override;
use App\Application\Outbound\Override\OverrideType;
use App\Domain\Outbound\Entity\Outbound;
use App\Domain\Outbound\Entity\ShadowsocksOutbound;

final readonly class OverrideSSPass extends Override
{

    public function override(Outbound $outbound, OverrideType $overrideType): Outbound
    {
        if (!($outbound instanceof ShadowsocksOutbound)) return $outbound;

        return $outbound->withPassword($overrideType->getValue());

    }
}