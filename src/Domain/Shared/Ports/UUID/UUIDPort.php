<?php

declare(strict_types=1);

namespace App\Domain\Shared\Ports\UUID;

use RuntimeException;

interface UUIDPort
{
    /**
     * Generate UUID
     *
     * @return string UUID
     *
     * @throws RuntimeException If unable to generate UUID
     */
    public function generate(): string;
}