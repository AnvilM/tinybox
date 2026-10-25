<?php

declare(strict_types=1);

namespace App\Application\Outbound\Selection\Property;

use App\Domain\Outbound\Entity\Outbound;
use InvalidArgumentException;

/**
 * A single selectable characteristic of an outbound (`type`, `tag`, ...).
 */
interface Property
{
    /**
     * Lower-case name used in expressions.
     */
    public function name(): string;

    public function kind(): ValueKind;

    /**
     * Normalizes and validates a value typed by the user.
     *
     * @throws InvalidArgumentException
     */
    public function parse(string $raw): string|int;

    /**
     * Value of this property for the given outbound, null if there is none.
     */
    public function valueOf(Outbound $outbound): string|int|null;

    /**
     * Values accepted by parse(), used in error messages.
     *
     * @return list<string>|null null - any value is accepted
     */
    public function allowedValues(): ?array;
}
