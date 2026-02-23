<?php

declare(strict_types=1);

namespace App\Core\Shared\VO\ReporterEvent;

enum TypeVO: string
{
    case Success = '[✓]';

    case Warning = '[!]';

    case Error = '[✗]';

    case Skipped = '[~]';

    case Step = '[*]';
}