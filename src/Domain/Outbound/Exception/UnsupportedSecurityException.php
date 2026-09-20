<?php

declare(strict_types=1);

namespace App\Domain\Outbound\Exception;

use App\Domain\Shared\Exception\CoreException;

final class UnsupportedSecurityException extends CoreException
{
    public function __construct(?string $security = null)
    {
        parent::__construct($security ? "Unsupported security: $security" : 'Unsupported security');
    }
}