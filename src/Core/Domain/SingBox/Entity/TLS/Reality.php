<?php

declare(strict_types=1);

namespace App\Core\Domain\SingBox\Entity\TLS;

use InvalidArgumentException;

final readonly class Reality
{
    private string $publicKey;
    private string $shortId;
    private bool $enabled;

    public function __construct(
        string $publicKey,
        string $shortId,
        bool   $enabled = true,
    )
    {
        $this->publicKey = $this->assertNonEmptyString($publicKey, 'public_key');
        $this->shortId = $this->assertNonEmptyString($shortId, 'short_id');
        $this->enabled = $enabled;
    }

    private function assertNonEmptyString(string $value, string $name): string
    {

        if (trim($value) === '') throw new InvalidArgumentException("$name is required");

        return trim($value);
    }

    public function toArray(): array
    {
        return [
            'enabled' => $this->enabled,
            'public_key' => $this->publicKey,
            'short_id' => $this->shortId,
        ];
    }

}
