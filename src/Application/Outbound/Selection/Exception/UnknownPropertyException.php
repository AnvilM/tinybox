<?php

declare(strict_types=1);

namespace App\Application\Outbound\Selection\Exception;

/**
 * Thrown when an expression refers to a property that is not registered.
 */
final class UnknownPropertyException extends SelectionException
{
    /**
     * @param list<string> $available Names of all registered properties
     * @param string|null $suggestion Closest registered name, if any
     */
    public function __construct(string $property, array $available, ?string $suggestion, string $source, int $position)
    {
        $message = "Unknown property '$property'.";

        if ($suggestion !== null) {
            $message .= " Did you mean '$suggestion'?";
        }

        $message .= ' Available: ' . implode(', ', $available);

        parent::__construct($message, $source, $position);
    }
}
