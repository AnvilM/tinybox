<?php

declare(strict_types=1);

namespace App\Domain\Shared\Exception;

use Exception;
use Throwable;

class CoreException extends Exception
{
    private string $debugMessage;

    public function __construct(string $message = "", string $debugMessage = "", int $code = 0, ?Throwable $previous = null)
    {
        $this->debugMessage = $debugMessage;
        parent::__construct($message, $code, $previous);
    }

    public function getDebugMessage(): string
    {
        return $this->debugMessage;
    }
}