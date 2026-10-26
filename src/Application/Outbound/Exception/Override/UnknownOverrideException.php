<?php

declare(strict_types=1);

namespace App\Application\Outbound\Exception\Override;

use App\Domain\Shared\Exception\CriticalException;

final class UnknownOverrideException extends CriticalException
{
    /**
     * @param list<string> $available Names of all overrides
     * @param string|null $suggestion The closest name, null if there is none
     * @param string $source Original SPEC string
     */
    public function __construct(string $name, array $available, ?string $suggestion, string $source)
    {
        $message = sprintf("Unknown override '%s'.", $name);

        if ($suggestion !== null) {
            $message .= sprintf(" Did you mean '%s'?", $suggestion);
        }

        $message .= sprintf(' Available: %s', implode(', ', $available));
        $message .= sprintf(" (override rule '%s')", $source);

        parent::__construct($message);
    }
}
