<?php

declare(strict_types=1);

namespace App\Domain\Outbound\Factory;

use App\Domain\Outbound\Entity\Outbound;
use App\Domain\Outbound\Exception\UnsupportedProtocolException;
use App\Domain\Outbound\Exception\UnsupportedSecurityException;
use App\Domain\Outbound\Exception\UnsupportedTransportException;
use App\Domain\Outbound\Factory\FromRawOutboundFactory\FromRawOutboundOutboundFactory;
use App\Domain\Outbound\VO\RawOutboundVO;
use InvalidArgumentException;

final readonly class OutboundFactory
{
    public function __construct(
        private FromRawOutboundOutboundFactory $fromRawOutboundOutboundFactory,
    )
    {
    }

    /**
     * Creates an Outbound entity from Raw outbound dto
     *
     * @param RawOutboundVO $rawOutbound Raw outbound dto
     *
     * @return Outbound The created Outbound entity
     *
     * @throws UnsupportedProtocolException
     * @throws UnsupportedSecurityException
     * @throws UnsupportedTransportException
     * @throws InvalidArgumentException
     */
    public function fromRawOutbound(RawOutboundVO $rawOutbound, ?string $id): Outbound
    {
        return $this->fromRawOutboundOutboundFactory->create($rawOutbound, $id);
    }
}