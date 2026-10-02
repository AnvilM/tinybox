<?php

declare(strict_types=1);

namespace App\Domain\Outbound\VO\Security;

use App\Domain\Shared\Trait\ComparesNullable;
use App\Domain\Shared\VO\Shared\NonEmptyStringVO;
use Override;

final readonly class RealitySecurityVO extends SecurityVO
{
    use ComparesNullable;


    private NonEmptyStringVO $publicKey;
    private ?NonEmptyStringVO $shortId;
    private ?NonEmptyStringVO $spiderX;


    public function __construct(
        NonEmptyStringVO  $serverName,
        NonEmptyStringVO  $publicKey,
        ?NonEmptyStringVO $shortId,
        ?NonEmptyStringVO $fingerprint,
        ?NonEmptyStringVO $spiderX
    )
    {
        parent::__construct($serverName, $fingerprint);

        $this->publicKey = $publicKey;
        $this->shortId = $shortId;
        $this->spiderX = $spiderX;
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

    public function getType(): SecurityTypeVO
    {
        return SecurityTypeVO::Reality;
    }

    public function getSpiderX(): ?NonEmptyStringVO
    {
        return $this->spiderX;
    }

    #[Override]
    public function withServerName(NonEmptyStringVO $serverName): static
    {
        return $this->cloneWith(['serverName' => $serverName]);
    }

    protected function cloneWith(array $changes): static
    {
        return new self(...array_merge([
            'serverName' => $this->getServerName(),
            'publicKey' => $this->publicKey,
            'shortId' => $this->shortId,
            'fingerprint' => $this->getFingerprint(),
            'spiderX' => $this->spiderX,
        ], $changes));
    }

    #[Override]
    public function withFingerprint(?NonEmptyStringVO $fingerprint): static
    {
        return $this->cloneWith(['fingerprint' => $fingerprint]);
    }

    public function withPublicKey(NonEmptyStringVO $publicKey): static
    {
        return $this->cloneWith(['publicKey' => $publicKey]);
    }

    public function withShortId(?NonEmptyStringVO $shortId): static
    {
        return $this->cloneWith(['shortId' => $shortId]);
    }

    public function withSpiderX(?NonEmptyStringVO $spiderX): static
    {
        return $this->cloneWith(['spiderX' => $spiderX]);
    }

}