<?php

declare(strict_types=1);

namespace App\Application\Outbound\Override;

use App\Domain\Outbound\Entity\Outbound;

abstract readonly class Override
{
    public abstract function override(Outbound $outbound, OverrideType $overrideType): Outbound;
}