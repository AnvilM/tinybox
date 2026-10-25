<?php

declare(strict_types=1);

namespace App\Application\Outbound\DTO\UseCase\BuildUrltestOutbounds;

use App\Application\Outbound\DTO\Export\CoreType;
use App\Application\Outbound\Urltest\UrltestGroupDefinition;
use App\Domain\Outbound\Collection\OutboundMap;
use Psl\Collection\VectorInterface;

final readonly class BuildUrltestOutboundsDTO
{
    /**
     * @param OutboundMap $outboundsMap Regular outbounds (passed the global selection and overrides)
     * @param VectorInterface<UrltestGroupDefinition> $definitions
     * @param CoreType $core Core the config is created for
     */
    public function __construct(
        public OutboundMap     $outboundsMap,
        public VectorInterface $definitions,
        public CoreType        $core,
    )
    {
    }
}
