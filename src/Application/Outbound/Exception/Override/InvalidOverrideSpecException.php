<?php

declare(strict_types=1);

namespace App\Application\Outbound\Exception\Override;

use App\Domain\Shared\Exception\CriticalException;

final class InvalidOverrideSpecException extends CriticalException
{
    public function __construct(string $spec)
    {
        parent::__construct(
            sprintf("Override rule must have the form ACTION[=VALUE]:EXPRESSION, got '%s'", $spec),
        );
    }
}
