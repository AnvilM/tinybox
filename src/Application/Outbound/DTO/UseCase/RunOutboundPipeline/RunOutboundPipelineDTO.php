<?php

declare(strict_types=1);

namespace App\Application\Outbound\DTO\UseCase\RunOutboundPipeline;

use App\Application\Outbound\DTO\Export\CoreType;
use App\Application\Outbound\Pipeline\PipelineStep;
use App\Domain\Outbound\Collection\OutboundMap;
use Psl\Collection\VectorInterface;

final readonly class RunOutboundPipelineDTO
{
    /**
     * @param OutboundMap $outbounds Initial set (passed the global selection)
     * @param VectorInterface<PipelineStep> $steps Steps in the order of flags
     * @param CoreType $core Core the config is created for
     */
    public function __construct(
        public OutboundMap     $outbounds,
        public VectorInterface $steps,
        public CoreType        $core,
    )
    {
    }
}
