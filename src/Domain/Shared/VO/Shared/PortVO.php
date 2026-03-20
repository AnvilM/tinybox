<?php

declare(strict_types=1);

namespace App\Domain\Shared\VO\Shared;

use InvalidArgumentException;

final readonly class PortVO
{
    private int $port;


    /**
     * Constructor
     *
     * @param int $port Port
     *
     * @throws InvalidArgumentException If invalid value provided e.g., port less than 0 or more than 65535
     */
    public function __construct(int $port)
    {
        if ($port < 0 || $port > 65535) throw new InvalidArgumentException();

        $this->port = $port;
    }

    
    public function getPort(): int
    {
        return $this->port;
    }
}