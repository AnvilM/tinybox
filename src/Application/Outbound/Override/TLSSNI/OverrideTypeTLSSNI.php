<?php

declare(strict_types=1);

namespace App\Application\Outbound\Override\TLSSNI;

use App\Application\Outbound\Override\OverrideType;
use App\Domain\Shared\VO\Shared\NonEmptyStringVO;

final readonly class OverrideTypeTLSSNI extends OverrideType
{
    public function __construct(private NonEmptyStringVO $sni)
    {
    }

    public function getSni(): NonEmptyStringVO
    {
        return $this->sni;
    }
}
