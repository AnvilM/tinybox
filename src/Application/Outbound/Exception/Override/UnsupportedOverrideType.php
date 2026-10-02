<?php

declare(strict_types=1);

namespace App\Application\Outbound\Exception\Override;

use App\Application\Outbound\Override\OverrideType;
use App\Domain\Shared\Exception\CriticalException;

final class UnsupportedOverrideType extends CriticalException
{
    public function __construct(OverrideType $overrideType)
    {
        parent::__construct("Unsupported override type", "Unsupported override type: " . $overrideType::class);
    }
}