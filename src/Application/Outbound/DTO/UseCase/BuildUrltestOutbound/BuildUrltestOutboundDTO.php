<?php

declare(strict_types=1);

namespace App\Application\Outbound\DTO\UseCase\BuildUrltestOutbound;

use App\Application\Outbound\DTO\Export\CoreType;
use App\Application\Outbound\Urltest\PreparedUrltestGroup;
use App\Domain\Outbound\Collection\OutboundMap;

final readonly class BuildUrltestOutboundDTO
{
    /**
     * @param OutboundMap $pool Current set of the pipeline (regular outbounds + urltests created earlier)
     * @param PreparedUrltestGroup $group Group to build
     * @param CoreType $core Core the config is created for
     */
    public function __construct(
        public OutboundMap          $pool,
        public PreparedUrltestGroup $group,
        public CoreType             $core,
    )
    {
    }
}
