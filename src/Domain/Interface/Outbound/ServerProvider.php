<?php

declare(strict_types=1);

namespace App\Domain\Interface\Outbound;

use App\Domain\Shared\VO\Shared\NonEmptyStringVO;

interface ServerProvider
{
    /**
     * Get outbound server
     *
     * @return NonEmptyStringVO Outbound server
     */
    public function getServer(): NonEmptyStringVO;


    /**
     * Create new server provider with specified server
     *
     * @param NonEmptyStringVO $server Server to create with
     *
     * @return static
     */
    public function withServer(NonEmptyStringVO $server): static;
}