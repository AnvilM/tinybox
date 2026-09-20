<?php

declare(strict_types=1);

namespace App\Domain\Shared\Ports\IO\Reporter;

use Iva\Output\Output;

interface ReporterInstancePort
{
    public function set(Output $output): void;

    public function get(): ReporterPort;
}