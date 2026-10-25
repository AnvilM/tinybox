<?php

declare(strict_types=1);

namespace App\Application\Outbound\Exception\Urltest;

use App\Domain\Shared\Exception\CriticalException;

final class EmptyUrltestGroupException extends CriticalException
{
    public function __construct(string $source)
    {
        parent::__construct(
            sprintf("Urltest group '%s' matched no outbounds", $source),
        );
    }
}
