<?php

declare(strict_types=1);

namespace App\Application\Shared\DTO\UseCase\CreateConfig;

use App\Application\Urltest\Group\UrltestGroup;
use App\Domain\Outbound\Collection\OutboundMap;
use Psl\Collection\Vector;
use Psl\Collection\VectorInterface;

final readonly class CreateConfigDTO
{
    /**
     * @param VectorInterface<UrltestGroup> $urltestGroups Empty vector - urltest is not added to the config
     */
    public function __construct(
        public OutboundMap     $outboundsMap,
        public ConfigType      $configType,
        public VectorInterface $urltestGroups = new Vector([]),
    )
    {
    }
}
