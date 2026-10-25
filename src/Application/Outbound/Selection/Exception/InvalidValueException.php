<?php

declare(strict_types=1);

namespace App\Application\Outbound\Selection\Exception;

/**
 * Thrown when a value is not acceptable for the property.
 */
final class InvalidValueException extends SelectionException
{
    /**
     * @param list<string>|null $allowed Values accepted by the property, null - any
     * @param string $reason Property's own explanation, used when there is no list of allowed values
     */
    public function __construct(string $value, string $property, ?array $allowed, string $reason, string $source, int $position)
    {
        $message = "Invalid value '$value' for property '$property'.";

        if ($allowed !== null) {
            $message .= ' Allowed: ' . implode(', ', $allowed);
        } elseif ($reason !== '') {
            $message .= " $reason";
        }

        parent::__construct($message, $source, $position);
    }
}
