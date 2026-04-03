<?php

declare(strict_types=1);

namespace App\Application\Shared\DTO\UseCase\CreateOutboundsFromSchemesMap;

use App\Domain\Scheme\Collection\SchemeMap;

final readonly class CreateOutboundsFromSchemesMapDTO
{
    public function __construct(
        public SchemeMap $schemeMap,
    )
    {
    }
}