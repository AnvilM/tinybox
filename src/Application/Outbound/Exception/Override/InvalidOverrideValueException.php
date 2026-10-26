<?php

declare(strict_types=1);

namespace App\Application\Outbound\Exception\Override;

use App\Domain\Shared\Exception\CriticalException;

final class InvalidOverrideValueException extends CriticalException
{
    /**
     * @param string $overrideName Name of the override
     * @param string $reason What is wrong with the value
     * @param string|null $source Original SPEC string, null if it is not known yet
     */
    public function __construct(
        public readonly string $overrideName,
        public readonly string $reason,
        ?string                $source = null,
    )
    {
        $message = sprintf("Invalid value for override '%s': %s", $overrideName, $reason);

        if ($source !== null) {
            $message .= sprintf(" (override rule '%s')", $source);
        }

        parent::__construct($message);
    }
}
