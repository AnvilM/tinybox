<?php

declare(strict_types=1);

namespace App\Application\Outbound\Pipeline;

/**
 * Marker of a parsed CLI definition that is a step of the outbound pipeline.
 *
 * Steps are executed in the order of the flags in the command line.
 */
interface PipelineStep
{
}
