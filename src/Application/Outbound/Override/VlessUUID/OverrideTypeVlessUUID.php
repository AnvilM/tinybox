<?php

declare(strict_types=1);

namespace App\Application\Outbound\Override\VlessUUID;

use App\Application\Outbound\Override\OverrideType;
use App\Domain\Shared\VO\Shared\NonEmptyStringVO;

final readonly class OverrideTypeVlessUUID extends OverrideType
{
    public function __construct(private NonEmptyStringVO $uuid)
    {
    }

    public function getUuid(): NonEmptyStringVO
    {
        return $this->uuid;
    }
}
