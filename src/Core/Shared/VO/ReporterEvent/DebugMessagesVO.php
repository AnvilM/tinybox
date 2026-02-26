<?php

declare(strict_types=1);

namespace App\Core\Shared\VO\ReporterEvent;

use App\Core\Domain\Shared\Collection\AbstractCollection;

/**
 * @extends AbstractCollection<string>
 */
final class DebugMessagesVO extends AbstractCollection
{
    public function getType(): string
    {
        return 'string';
    }

}