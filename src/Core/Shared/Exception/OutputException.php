<?php

declare(strict_types=1);

namespace App\Core\Shared\Exception;

use App\Core\Shared\VO\Shared\Output\OutputFMTypeVO;
use RuntimeException;

class OutputException extends RuntimeException
{
    public function __construct(string $message, public OutputFMTypeVO $type, public ?string $debugMessage = null)
    {
        parent::__construct($message);
    }
}