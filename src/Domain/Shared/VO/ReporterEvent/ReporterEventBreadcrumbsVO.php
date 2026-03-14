<?php

declare(strict_types=1);

namespace App\Domain\Shared\VO\ReporterEvent;


use Ramsey\Collection\AbstractCollection;

final class ReporterEventBreadcrumbsVO extends AbstractCollection
{
    public function getType(): string
    {
        return 'string';
    }

}