<?php

declare(strict_types=1);

namespace App\Domain\Interface\Outbound;

use App\Domain\Outbound\VO\Security\SecurityVO;

interface SecurityProvider
{
    /**
     * Get outbound security
     *
     * @return SecurityVO|null Outbound security
     */
    public function getSecurity(): ?SecurityVO;


    /**
     * Create nwe security provider with specified security
     *
     * @param SecurityVO|null $security Security to create with
     *
     * @return static
     */
    public function withSecurity(?SecurityVO $security): static;
}