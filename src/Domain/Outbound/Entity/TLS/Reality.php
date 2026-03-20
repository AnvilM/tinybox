<?php

declare(strict_types=1);

namespace App\Domain\Outbound\Entity\TLS;

use App\Domain\Shared\VO\Shared\NonEmptyStringVO;

final readonly class Reality
{
    private NonEmptyStringVO $publicKey;
    private NonEmptyStringVO $shortId;
    private bool $enabled;

    
    public function __construct(NonEmptyStringVO $publicKey, NonEmptyStringVO $shortId, bool $enabled)
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
        return [
            'enabled' => $this->enabled,
            'public_key' => $this->publicKey->getValue(),
            'short_id' => $this->shortId->getValue(),
        ];
    }
}