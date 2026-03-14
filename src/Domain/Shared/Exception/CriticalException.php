<?php

declare(strict_types=1);

namespace App\Domain\Shared\Exception;

class CriticalException extends CoreException
{
    public function __construct(string $message = "", public ?string $debugMessage = null)
    {
        parent::__construct($message);
    }

}