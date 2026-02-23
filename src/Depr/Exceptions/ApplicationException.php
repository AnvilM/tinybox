<?php

declare(strict_types=1);

namespace App\Core\Exceptions;

use Exception;
use Throwable;

final class ApplicationException extends Exception
{
    public readonly string $debugMessage;
    public function __construct(string $message = "",string $debugMessage = "", int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        
        $this->debugMessage = trim($debugMessage);
    }
}