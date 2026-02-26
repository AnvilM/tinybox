<?php

declare(strict_types=1);

namespace App\Core\Domain\SingBox\Collection;

use App\Core\Domain\Shared\Collection\AbstractCollection;
use App\Core\Domain\SingBox\Entity\Outbound\Outbound;

/**
 * @extends AbstractCollection<Outbound>
 */
final class OutboundCollection extends AbstractCollection
{
    public function getType(): string
    {
        return Outbound::class;
    }

}