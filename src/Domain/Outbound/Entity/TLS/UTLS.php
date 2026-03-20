<?php

declare(strict_types=1);

namespace App\Domain\Outbound\Entity\TLS;

use App\Domain\Shared\VO\Shared\NonEmptyStringVO;

final readonly class UTLS
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
}