<?php

declare(strict_types=1);

namespace App\Domain\Shared\VO\Shared;

use InvalidArgumentException;
use Psl\Type\Exception\CoercionException;
use function Psl\Type\non_empty_string;

readonly class NonEmptyStringVO
{
    private string $value;

    /**
     * Constructor
     *
     * @throws InvalidArgumentException If invalid string provided
     */
    public function __construct(string $value)
    {
        try {
            $this->value = non_empty_string()->coerce($value);
        } catch (CoercionException) {
            throw new InvalidArgumentException();
        }
    }

    public function getValue(): string
    {
        return $this->value;
    }
}