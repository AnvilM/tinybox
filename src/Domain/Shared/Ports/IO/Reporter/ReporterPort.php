<?php

declare(strict_types=1);

namespace App\Domain\Shared\Ports\IO\Reporter;

use App\Domain\Shared\ReporterEvent\ReporterEventInterface;

interface ReporterPort
{
    public function notify(ReporterEventInterface $reporterEvent): void;
}