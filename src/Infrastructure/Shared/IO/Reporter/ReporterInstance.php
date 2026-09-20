<?php

declare(strict_types=1);

namespace App\Infrastructure\Shared\IO\Reporter;

use App\Domain\Shared\Ports\IO\Reporter\ReporterInstancePort;
use App\Domain\Shared\Ports\IO\Reporter\ReporterPort;
use Iva\Output\Output;

final readonly class ReporterInstance implements ReporterInstancePort
{
    private ReporterPort $reporter;

    public function set(Output $output): void
    {
        $this->reporter = Reporter::fromOutput($output);
    }

    public function get(): ReporterPort
    {
        return $this->reporter;
    }
}