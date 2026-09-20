<?php

declare(strict_types=1);

namespace App\Domain\Shared\VO\ReporterEvent;

enum ReporterEventVerbosity: int
{
    case Quiet = -1;
    case Normal = 0;
    case Verbose = 1;
    case VeryVerbose = 2;
    case Debug = 3;

    public function atLeast(self $threshold): bool
    {
        return $this->value >= $threshold->value;
    }
}
