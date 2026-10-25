<?php

declare(strict_types=1);

namespace App\Application\Outbound\Exception\Detour;

use App\Domain\Shared\Exception\CriticalException;

final class InvalidDetourSpecException extends CriticalException
{
    public function __construct(string $spec)
    {
        parent::__construct(
            sprintf("Detour rule must have the form DETOUR_TAG:EXPRESSION, got '%s'", $spec),
        );
    }
}
