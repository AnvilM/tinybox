<?php

declare(strict_types=1);

namespace App\Domain\Outbound\Factory;

use App\Domain\Outbound\Collection\OutboundMap;
use App\Domain\Outbound\Entity\UrltestOutbound;
use App\Domain\Outbound\VO\Urltest\UrltestParametersVO;
use App\Domain\Shared\Ports\UUID\UUIDPort;

final readonly class UrltestOutboundFactory
{
    public function __construct(
        private UUIDPort $uuidPort,
    )
    {
    }

    public function create(string $tag, OutboundMap $members, UrltestParametersVO $parameters): UrltestOutbound
    {
        return new UrltestOutbound($tag, $this->uuidPort->generateNonEmptyString(), $members, $parameters);
    }
}
