<?php

declare(strict_types=1);

namespace App\Application\Outbound\Override;

use App\Domain\Shared\VO\Shared\NonEmptyStringVO;

abstract readonly class OverrideType
{
    public function __construct(
        private NonEmptyStringVO $value,
    )
    {
    }


    public function getValue(): NonEmptyStringVO
    {
        return $this->value;
    }

}