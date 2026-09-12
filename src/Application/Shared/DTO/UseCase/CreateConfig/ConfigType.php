<?php

declare(strict_types=1);

namespace App\Application\Shared\DTO\UseCase\CreateConfig;

use App\Application\Outbound\Export\CoreType;

enum ConfigType
{
    case SingBox;
    case Xray;

    
    public function toCoreType(): CoreType
    {
        return match ($this) {
            self::SingBox => CoreType::SingBox,
            self::Xray => CoreType::Xray
        };
    }
}
