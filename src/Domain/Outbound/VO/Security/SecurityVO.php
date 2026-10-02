<?php

declare(strict_types=1);

namespace App\Domain\Outbound\VO\Security;

use App\Domain\Interface\Shared\Equable;
use App\Domain\Shared\Trait\ComparesNullable;
use App\Domain\Shared\VO\Shared\NonEmptyStringVO;

abstract readonly class SecurityVO implements Equable
{
    use ComparesNullable;

    private NonEmptyStringVO $serverName;
    private ?NonEmptyStringVO $fingerprint;

    public function __construct(NonEmptyStringVO $serverName, ?NonEmptyStringVO $fingerprint)
    {
        $this->serverName = $serverName;
        $this->fingerprint = $fingerprint;
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
            $this->getServerName()->equals($other->getServerName()) &&
            $this->equalsNullable($this->getFingerprint(), $other->getFingerprint());
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
     * @return NonEmptyStringVO|null Fingerprint
     */
    public function getFingerprint(): ?NonEmptyStringVO
    {
        return $this->fingerprint;
    }


    abstract public function getType(): SecurityTypeVO;

    /**
     * Create a copy with a new server name
     *
     * @param NonEmptyStringVO $serverName New server name
     *
     * @return static Copy with new server name
     */
    abstract public function withServerName(NonEmptyStringVO $serverName): static;

    /**
     * Create a copy with a new fingerprint
     *
     * @param NonEmptyStringVO|null $fingerprint New fingerprint
     *
     * @return static Copy with new fingerprint
     */
    abstract public function withFingerprint(?NonEmptyStringVO $fingerprint): static;


    /**
     * Create a copy of the current object with overridden constructor arguments
     *
     * NOTE: Keys of $changes must match constructor parameter names.
     *
     * @param array<string, mixed> $changes Constructor arguments to override
     */
    abstract protected function cloneWith(array $changes): static;
}