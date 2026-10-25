<?php

declare(strict_types=1);

namespace App\Application\Urltest\Group;

use App\Domain\Outbound\Collection\OutboundMap;

/**
 * What came out of the selection: a urltest tag and its members.
 */
final readonly class UrltestGroup
{
    /**
     * @param string|null $tag Urltest tag, null - will be resolved while creating the config
     * @param OutboundMap $outbounds Group members (not empty)
     */
    public function __construct(
        public ?string     $tag,
        public OutboundMap $outbounds,
    )
    {
    }
}
