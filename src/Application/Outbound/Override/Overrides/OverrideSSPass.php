<?php

declare(strict_types=1);

namespace App\Application\Outbound\Override\Overrides;

use App\Application\Outbound\Override\Override;
use App\Domain\Outbound\Entity\Outbound;
use App\Domain\Outbound\Entity\ShadowsocksOutbound;
use App\Domain\Shared\VO\Shared\NonEmptyStringVO;

final readonly class OverrideSSPass extends Override
{
    public function __construct(private NonEmptyStringVO $password)
    {
    }

    public function override(Outbound $outbound): Outbound
    {
        if (!($outbound instanceof ShadowsocksOutbound)) return $outbound;

        return $outbound->withPassword($this->password);

    }
}