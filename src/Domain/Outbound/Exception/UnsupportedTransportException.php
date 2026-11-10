<?php

declare(strict_types=1);

namespace App\Domain\Outbound\Exception;

use App\Domain\Shared\Exception\CoreException;

final class UnsupportedTransportException extends CoreException
{
    public function __construct(?string $transport = null)
    {
        parent::__construct($transport ? "Unsupported transport: $transport" : 'Unsupported transport');
    }
}