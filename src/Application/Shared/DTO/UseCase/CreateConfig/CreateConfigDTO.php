<?php

declare(strict_types=1);

namespace App\Application\Shared\DTO\UseCase\CreateConfig;

use App\Domain\Outbound\Collection\OutboundMap;

final readonly class CreateConfigDTO
{
    /**
     * @param OutboundMap $outboundsMap Regular outbounds and urltest outbounds
     */
    public function __construct(
        public OutboundMap $outboundsMap,
        public ConfigType  $configType,
    )
    {
    }
}
