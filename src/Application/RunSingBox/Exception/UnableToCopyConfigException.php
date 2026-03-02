<?php

declare(strict_types=1);

namespace App\Application\RunSingBox\Exception;

use App\Core\Shared\Exception\CriticalException;

final class UnableToCopyConfigException extends CriticalException
{
    public function __construct(string $message = "", ?string $debugMessage = null)
    {
        parent::__construct("Unable to copy config: " . $message, $debugMessage);
    }
}