<?php

declare(strict_types=1);

namespace App\Core\Shared\Exception;

use RuntimeException;

class CriticalException extends RuntimeException
{
    public function __construct(string $message = "", public ?string $debugMessage = null)
    {
        parent::__construct($message);
    }

}