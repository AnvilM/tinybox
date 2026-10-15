<?php

declare(strict_types=1);

namespace App\Application\Outbound\Override\SSPass;

use App\Application\Outbound\Override\OverrideType;
use App\Domain\Shared\VO\Shared\NonEmptyStringVO;

final readonly class OverrideTypeSSPass extends OverrideType
{
    public function __construct(private NonEmptyStringVO $password)
    {
    }

    public function getPassword(): NonEmptyStringVO
    {
        return $this->password;
    }
}
