<?php

declare(strict_types=1);

namespace App\Domain\Shared\VO\Outbound\Security;

use App\Domain\Interface\Shared\Equable;
use App\Domain\Outbound\VO\Security\FingerprintVO;
use App\Domain\Shared\VO\Shared\NonEmptyStringVO;

abstract readonly class SecurityVO implements Equable
{
    private NonEmptyStringVO $serverName;
    private ?FingerprintVO $fingerprint;

    public function __construct(NonEmptyStringVO $serverName, ?FingerprintVO $fingerprint)
    {
        $this->serverName = $serverName;
        $this->fingerprint = $fingerprint;
    }


    /**
     * Get server name
     *
     * @return NonEmptyStringVO Server name
     */
    public function getServerName(): NonEmptyStringVO
    {
        return $this->serverName;
    }


    /**
     * Get fingerprint
     *
     * @return FingerprintVO|null Fingerprint
     */
    public function getFingerprint(): ?FingerprintVO
    {
        return $this->fingerprint;
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
        return $other instanceof static &&
            $this->serverName->equals($other->serverName) &&
            $this->fingerprint === $other->fingerprint;
    }


}