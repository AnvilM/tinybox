<?php

declare(strict_types=1);

namespace App\Domain\Outbound\Entity\TLS;

use App\Domain\Shared\VO\Shared\NonEmptyStringVO;

final readonly class TLS
{
    private bool $enabled;
    private NonEmptyStringVO $serverName;
    private ?Reality $reality;
    private ?UTLS $utls;


    public function __construct(NonEmptyStringVO $serverName, Reality $reality, UTLS $utls, bool $enabled)
    {
        $this->serverName = $serverName;
        $this->reality = $reality;
        $this->utls = $utls;
        $this->enabled = $enabled;
    }


    /**
     * Convert tls entity to array
     *
     * @return array Tls entity as array
     */
    public function toArray(): array
    {
        return array_filter([
            'enabled' => $this->enabled,
            'server_name' => $this->serverName->getValue(),
            'reality' => $this->reality?->toArray(),
            'utls' => $this->utls?->toArray(),
        ], static fn($value) => $value !== null);
    }
}