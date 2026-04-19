<?php

declare(strict_types=1);

namespace App\Domain\Outbound\Entity\TLS;

use App\Domain\Interface\Shared\Equable;
use App\Domain\Shared\VO\Shared\NonEmptyStringVO;

final readonly class UTLS implements Equable
{
    private NonEmptyStringVO $fingerprint;
    private bool $enabled;


    public function __construct(NonEmptyStringVO $fingerprint, bool $enabled)
    {
        $this->fingerprint = $fingerprint;
        $this->enabled = $enabled;
    }


    /**
     * Convert utls entity to array
     *
     * @return array Utls entity as array
     */
    public function toArray(): array
    {
        return [
            'enabled' => $this->enabled,
            'fingerprint' => $this->fingerprint->getValue(),
        ];
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
            $this->fingerprint->equals($other->fingerprint) &&
            $this->enabled === $other->enabled;
    }
}