<?php

declare(strict_types=1);

namespace App\Domain\Outbound\Entity\TLS;

use App\Domain\Interface\Shared\Equable;
use App\Domain\Shared\Trait\ComparesNullable;
use App\Domain\Shared\VO\Shared\NonEmptyStringVO;

final readonly class TLS implements Equable
{
    use ComparesNullable;


    private bool $enabled;
    private NonEmptyStringVO $serverName;
    private ?Reality $reality;
    private ?UTLS $utls;


    public function __construct(NonEmptyStringVO $serverName, Reality $reality, UTLS $utls, bool $enabled)
    {
        $this->serverName = $serverName;
        $this->reality = $reality;
        $this->utls = $utls;
        $this->enabled = $enabled;
    }


    /**
     * Convert tls entity to array
     *
     * @return array Tls entity as array
     */
    public function toArray(): array
    {
        return array_filter([
            'enabled' => $this->enabled,
            'server_name' => $this->serverName->getValue(),
            'reality' => $this->reality?->toArray(),
            'utls' => $this->utls?->toArray(),
        ], static fn($value) => $value !== null);
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
        return $other instanceof self &&
            $this->enabled === $other->enabled &&
            $this->serverName->equals($other->serverName) &&
            $this->equalsNullable($this->reality, $other->reality) &&
            $this->equalsNullable($this->utls, $other->utls);
    }
}