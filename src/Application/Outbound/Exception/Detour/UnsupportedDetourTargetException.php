<?php

declare(strict_types=1);

namespace App\Application\Outbound\Exception\Detour;

use App\Domain\Shared\Exception\CriticalException;

final class UnsupportedDetourTargetException extends CriticalException
{
    public function __construct(string $tag)
    {
        parent::__construct(
            sprintf("Detour outbound '%s' is a urltest and cannot be used as detour in xray", $tag),
        );
    }
}
