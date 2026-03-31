<?php

declare(strict_types=1);

namespace App\Domain\Interface\Subscription;

use App\Domain\Outbound\Entity\Outbound;

interface DetourProvider
{
    /**
     * Set outbound detour
     *
     * @param Outbound $detour Outbound to use as detour
     */
    public function setDetour(Outbound $detour): void;
}