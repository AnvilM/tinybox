<?php

declare(strict_types=1);

namespace App\Application\Shared\DTO\UseCase\CreateOutboundFromScheme;

use App\Domain\Scheme\Entity\Scheme;

final readonly class CreateOutboundFromSchemeDTO
{
    public function __construct(
        public Scheme $scheme
    )
    {
    }
}