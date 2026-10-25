<?php

declare(strict_types=1);

namespace App\Application\Outbound\Exception\Detour;

use App\Domain\Shared\Exception\CriticalException;

final class EmptyDetourRuleException extends CriticalException
{
    public function __construct(string $source)
    {
        parent::__construct(
            sprintf("Detour rule '%s' matched no outbounds", $source),
        );
    }
}
