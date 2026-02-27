<?php

declare(strict_types=1);

namespace App\Core\Shared\Ports\IO\Reporter;

use App\Core\Shared\ReporterEvent\ReporterEventInterface;

interface ReporterPort
{
    public function notify(ReporterEventInterface $reporterEvent): void;
}