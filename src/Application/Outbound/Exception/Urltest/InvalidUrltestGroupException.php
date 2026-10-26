<?php

declare(strict_types=1);

namespace App\Application\Outbound\Exception\Urltest;

use App\Domain\Shared\Exception\CriticalException;

final class InvalidUrltestGroupException extends CriticalException
{
    public function __construct(string $spec)
    {
        parent::__construct(
            sprintf("Urltest group must have the form TAG:EXPRESSION, got '%s'", $spec),
        );
    }
}
