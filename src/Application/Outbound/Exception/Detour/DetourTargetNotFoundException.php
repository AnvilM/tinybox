<?php

declare(strict_types=1);

namespace App\Application\Outbound\Exception\Detour;

use App\Domain\Shared\Exception\CriticalException;

final class DetourTargetNotFoundException extends CriticalException
{
    public function __construct(string $tag, string $source)
    {
        parent::__construct(
            sprintf("Outbound with tag '%s' not found (detour rule '%s'). Try to remove selection flags", $tag, $source),
        );
    }
}
