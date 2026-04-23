<?php

declare(strict_types=1);

namespace App\Domain\Outbound\Entity\TLS;

use App\Domain\Interface\Shared\Equable;
use App\Domain\Shared\Trait\ComparesNullable;
use App\Domain\Shared\VO\Shared\NonEmptyStringVO;

final readonly class Reality implements Equable
{
    use ComparesNullable;


    private NonEmptyStringVO $publicKey;
    private ?NonEmptyStringVO $shortId;
    private bool $enabled;


    public function __construct(NonEmptyStringVO $publicKey, ?NonEmptyStringVO $shortId, bool $enabled)
    {
        $this->publicKey = $publicKey;
        $this->shortId = $shortId;
        $this->enabled = $enabled;
    }


    /**
     * Convert entity to array
     *
     * @return array Reality entity as array
     */
    public function toArray(): array
    {
        $array = [
            'enabled' => $this->enabled,
            'public_key' => $this->publicKey->getValue(),
        ];

        if ($this->shortId !== null) $array['short_id'] = $this->shortId->getValue();

        return $array;

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
            $this->publicKey->equals($other->publicKey) &&
            $this->equalsNullable($this->shortId, $other->shortId) &&
            $this->enabled === $other->enabled;
    }
}