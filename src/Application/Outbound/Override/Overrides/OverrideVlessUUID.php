<?php

declare(strict_types=1);

namespace App\Application\Outbound\Override\Overrides;

use App\Application\Outbound\Override\Override;
use App\Domain\Outbound\Entity\Outbound;
use App\Domain\Outbound\Entity\VlessOutbound;
use App\Domain\Shared\VO\Shared\NonEmptyStringVO;

final readonly class OverrideVlessUUID extends Override
{
    public function __construct(private NonEmptyStringVO $uuid)
    {
    }

    public function override(Outbound $outbound): Outbound
    {
        if (!($outbound instanceof VlessOutbound)) return $outbound;

        return $outbound->withUUID($this->uuid);
    }


}