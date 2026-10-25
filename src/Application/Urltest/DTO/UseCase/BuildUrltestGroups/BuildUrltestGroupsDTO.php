<?php

declare(strict_types=1);

namespace App\Application\Urltest\DTO\UseCase\BuildUrltestGroups;

use App\Application\Urltest\Group\UrltestGroupDefinition;
use App\Domain\Outbound\Collection\OutboundMap;
use Psl\Collection\VectorInterface;

final readonly class BuildUrltestGroupsDTO
{
    /**
     * @param OutboundMap $outboundsMap Outbounds to build groups from
     *                                  (already passed the global selection and override)
     * @param VectorInterface<UrltestGroupDefinition> $definitions
     */
    public function __construct(
        public OutboundMap     $outboundsMap,
        public VectorInterface $definitions,
    )
    {
    }
}
