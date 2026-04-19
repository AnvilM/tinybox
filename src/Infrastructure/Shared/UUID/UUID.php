<?php

declare(strict_types=1);

namespace App\Infrastructure\Shared\UUID;

use App\Domain\Shared\Ports\UUID\UUIDPort;
use DateTimeImmutable;
use Ramsey\Uuid\Exception\UnsupportedOperationException;
use RuntimeException;

final readonly class UUID implements UUIDPort
{
    public function generate(): string
    {
        try {
            return \Ramsey\Uuid\Uuid::uuid7(
                new DateTimeImmutable()
            )->toString();
        } catch (UnsupportedOperationException) {
            throw new RuntimeException('UUID generation failed');
        }
    }
}