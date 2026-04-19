<?php

declare(strict_types=1);

namespace App\Domain\Shared\VO\Shared;

use App\Domain\Interface\Shared\Equable;
use InvalidArgumentException;
use Psl\Type\Exception\CoercionException;
use function Psl\Type\non_empty_string;

readonly class NonEmptyStringVO implements Equable
{
    private string $value;

    /**
     * Constructor
     *
     * @throws InvalidArgumentException If invalid string provided
     */
    public function __construct(?string $value)
    {
        try {
            $this->value = non_empty_string()->coerce($value);
        } catch (CoercionException) {
            throw new InvalidArgumentException("Invalid string: " . "'" . ($value ?? 'null') . "'");
        }
    }

    /**
     * Check if other object is equals with current
     *
     * @param mixed $other Other object
     *
     * @return bool True if equals
     */
    public function equals(mixed $other): bool
    {
        return $other instanceof static &&
            $this->getValue() === $other->getValue();
    }

    public function getValue(): string
    {
        return $this->value;
    }
}