<?php

declare(strict_types=1);

namespace App\Application\Outbound\DTO\UseCase\ApplyDetours;

use App\Application\Outbound\Detour\DetourDefinition;
use App\Application\Outbound\DTO\Export\CoreType;
use App\Domain\Outbound\Collection\OutboundMap;
use Psl\Collection\VectorInterface;

final readonly class ApplyDetoursDTO
{
    /**
     * @param OutboundMap $outbounds Outbounds to look for the targets and the subjects in
     *                               (export: regular + urltest; test: after the global selection)
     * @param VectorInterface<DetourDefinition> $definitions Rules in the order of flags
     * @param CoreType $core Core the config is created for
     */
    public function __construct(
        public OutboundMap     $outbounds,
        public VectorInterface $definitions,
        public CoreType        $core,
    )
    {
    }
}
