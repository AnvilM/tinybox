<?php

declare(strict_types=1);

namespace App\Application\Outbound\Exception\Override;

use App\Domain\Shared\Exception\CriticalException;

final class EmptyOverrideRuleException extends CriticalException
{
    public function __construct(string $source)
    {
        parent::__construct(
            sprintf("Override rule '%s' matched no outbounds", $source),
        );
    }
}
