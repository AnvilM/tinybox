<?php

declare(strict_types=1);

namespace App\Domain\Outbound\Exception;

use App\Domain\Shared\Exception\CoreException;

final class UnsupportedOutboundTypeException extends CoreException
{
    public function __construct(?string $type)
    {
        parent::__construct("Unsupported Outbound Type: " . "'" . ($type ?? 'null') . "'");
    }
}