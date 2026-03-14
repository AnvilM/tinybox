<?php

declare(strict_types=1);

namespace App\Domain\Shared\VO\ReporterEvent;

enum ReporterEventTypeVO: string
{
    case Success = '[✓]';

    case Warning = '[!]';

    case Error = '[✗]';

    case Skipped = '[~]';

    case Step = '[*]';
}