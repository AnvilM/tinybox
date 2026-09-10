<?php

declare(strict_types=1);

namespace App\Domain\Outbound\Factory\FromRawOutboundFactory\Outbound;

use App\Domain\Outbound\DTO\RawOutboundDTO;
use App\Domain\Outbound\Entity\VlessOutbound;
use App\Domain\Outbound\Exception\UnsupportedSecurityException;
use App\Domain\Outbound\Exception\UnsupportedTransportException;
use App\Domain\Outbound\Factory\FromRawOutboundFactory\Shared\Security\FromRawOutboundSecurityFactory;
use App\Domain\Outbound\Factory\FromRawOutboundFactory\Shared\Transport\FromRawOutboundTransportFactory;
use App\Domain\Shared\VO\Shared\NonEmptyStringVO;
use App\Domain\Shared\VO\Shared\PortVO;
use InvalidArgumentException;

final readonly class FromRawOutboundVlessOutboundFactory
{
    /**
     * Creates a Vless outbound from raw outbound dto
     *
     * @param RawOutboundDTO $rawOutbound Raw outbound dto
     *
     * @return VlessOutbound Created vless outbound entity
     *
     * @throws InvalidArgumentException If required fields are missing or provided invalid fields
     * @throws UnsupportedSecurityException If security is unsupported
     * @throws UnsupportedTransportException If transport type is unsupported
     */
    public static function create(RawOutboundDTO $rawOutbound): VlessOutbound
    {
        return new VlessOutbound(
            $rawOutbound->tag,
            new NonEmptyStringVO($rawOutbound->server),
            new PortVO($rawOutbound->server_port),
            new NonEmptyStringVO($rawOutbound->uuid),
            $rawOutbound->flow === null ? null : new NonEmptyStringVO($rawOutbound->flow),
            $rawOutbound->security === null ? null : FromRawOutboundSecurityFactory::create($rawOutbound),
            $rawOutbound->transportType === null || $rawOutbound->transportType === 'tcp'
                ? null
                : FromRawOutboundTransportFactory::create($rawOutbound),
        );
    }
}