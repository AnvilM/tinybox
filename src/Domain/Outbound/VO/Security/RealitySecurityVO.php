<?php

declare(strict_types=1);

namespace App\Domain\Outbound\VO\Security;

use App\Domain\Shared\Trait\ComparesNullable;
use App\Domain\Shared\VO\Shared\NonEmptyStringVO;

final readonly class RealitySecurityVO extends SecurityVO
{
    use ComparesNullable;


    private NonEmptyStringVO $publicKey;
    private ?NonEmptyStringVO $shortId;


    public function __construct(
        NonEmptyStringVO  $serverName,
        NonEmptyStringVO  $publicKey,
        ?NonEmptyStringVO $shortId,
        ?NonEmptyStringVO $fingerprint
    )
    {
        parent::__construct($serverName, $fingerprint);

        $this->publicKey = $publicKey;
        $this->shortId = $shortId;
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
        return parent::equals($other) &&
            $this->publicKey->equals($other->publicKey) &&
            $this->equalsNullable($this->shortId, $other->shortId);
    }

    public function getPublicKey(): NonEmptyStringVO
    {
        return $this->publicKey;
    }

    public function getShortId(): ?NonEmptyStringVO
    {
        return $this->shortId;
    }


}