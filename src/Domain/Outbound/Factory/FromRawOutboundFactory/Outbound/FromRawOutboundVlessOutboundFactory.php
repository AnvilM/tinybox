<?php

declare(strict_types=1);

namespace App\Domain\Outbound\Factory\FromRawOutboundFactory\Outbound;

use App\Domain\Outbound\Entity\VlessOutbound;
use App\Domain\Outbound\Exception\UnsupportedSecurityException;
use App\Domain\Outbound\Exception\UnsupportedTransportException;
use App\Domain\Outbound\Factory\FromRawOutboundFactory\Shared\Security\FromRawOutboundSecurityFactory;
use App\Domain\Outbound\Factory\FromRawOutboundFactory\Shared\Transport\FromRawOutboundTransportFactory;
use App\Domain\Outbound\VO\RawOutboundVO;
use App\Domain\Shared\VO\Shared\NonEmptyStringVO;
use App\Domain\Shared\VO\Shared\PortVO;
use InvalidArgumentException;

final readonly class FromRawOutboundVlessOutboundFactory
{

    public function __construct(
        private FromRawOutboundSecurityFactory  $fromRawOutboundSecurityFactory,
        private FromRawOutboundTransportFactory $fromRawOutboundTransportFactory,
    )
    {
    }

    /**
     * Creates a Vless outbound from raw outbound dto
     *
     * @param RawOutboundVO $rawOutbound Raw outbound dto
     *
     * @return VlessOutbound Created vless outbound entity
     *
     * @throws InvalidArgumentException If required fields are missing or provided invalid fields
     * @throws UnsupportedSecurityException If security is unsupported
     * @throws UnsupportedTransportException If transport type is unsupported
     */
    public function create(RawOutboundVO $rawOutbound, NonEmptyStringVO $id): VlessOutbound
    {
        return new VlessOutbound(
            $rawOutbound->tag,
            $id,
            new NonEmptyStringVO($rawOutbound->server),
            new PortVO($rawOutbound->server_port),
            new NonEmptyStringVO($rawOutbound->uuid),
            $rawOutbound->flow === null ? null : new NonEmptyStringVO($rawOutbound->flow),
            $rawOutbound->security === null ? null : $this->fromRawOutboundSecurityFactory->create($rawOutbound),
            $rawOutbound->transportType === null || $rawOutbound->transportType === 'tcp' ? null : $this->fromRawOutboundTransportFactory->create($rawOutbound),
        );
    }
}