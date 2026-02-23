<?php

declare(strict_types=1);

namespace App\Core\Shared\VO\ReporterEvent;

use App\Core\Shared\Collection\AbstractCollection;

final class BreadcrumbsVO extends AbstractCollection
{
    public function getType(): string
    {
        return 'string';
    }

}