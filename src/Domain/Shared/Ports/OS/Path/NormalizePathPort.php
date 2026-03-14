<?php

declare(strict_types=1);

namespace App\Domain\Shared\Ports\OS\Path;

use InvalidArgumentException;
use RuntimeException;
use UnexpectedValueException;

interface NormalizePathPort
{
    /**
     * Normalizes a path.
     *
     * Expands ~ and ~user, substitutes environment variables $VAR,
     * normalizes slashes, removes '.' and '..'.
     *
     * @param string $path Input path
     *
     * @return string Normalized path
     *
     * @throws InvalidArgumentException If the specified user does not exist
     * @throws RuntimeException If HOME env variable is not set
     * @throws UnexpectedValueException If HOME env variable is not string
     */
    public function execute(string $path): string;
}