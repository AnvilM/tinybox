<?php

declare(strict_types=1);

namespace App\Application\Shared\DTO\UseCase\CreateConfig;

use App\Domain\Outbound\Collection\OutboundMap;

final readonly class CreateConfigDTO
{
    public function __construct(
        public OutboundMap  $outboundsMap,
        public ConfigType   $configType,
        public ?OutboundMap $urltestOutbounds = null
    )
    {
    }
}