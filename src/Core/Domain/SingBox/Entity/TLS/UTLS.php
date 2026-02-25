<?php

declare(strict_types=1);

namespace App\Core\Domain\SingBox\Entity\TLS;

use InvalidArgumentException;

final readonly class UTLS
{
    private string $fingerprint;
    private bool $enabled;

    public function __construct(
        string $fingerprint,
        bool   $enabled = true,
    )
    {
        $this->fingerprint = $this->assertNonEmptyString($fingerprint, 'fingerprint');
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
            'fingerprint' => $this->fingerprint,
        ];
    }
}