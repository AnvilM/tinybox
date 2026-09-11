<?php

declare(strict_types=1);

namespace App\Domain\Shared\Ports\UUID;

use App\Domain\Shared\VO\Shared\NonEmptyStringVO;

interface UUIDPort
{
    /**
     * Generate UUID
     *
     * @return string UUID
     */
    public function generate(): string;

    /**
     * Generate UUID
     *
     * @return NonEmptyStringVO UUID
     */
    public function generateNonEmptyString(): NonEmptyStringVO;
}