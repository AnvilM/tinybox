<?php

declare(strict_types=1);

namespace App\Application\Outbound\UseCase\CreateOutboundFromScheme;

use App\Application\Exception\Shared\Scheme\CreateSchemeEntityFromString\UnableToParseRawSchemeStringException;
use App\Application\Shared\Ports\Outbound\Parser\RawOutboundParserPort;
use App\Domain\Outbound\Entity\Outbound;
use App\Domain\Outbound\Exception\UnsupportedProtocolException;
use App\Domain\Outbound\Exception\UnsupportedSecurityException;
use App\Domain\Outbound\Exception\UnsupportedTransportException;
use App\Domain\Outbound\Factory\OutboundFactory;
use InvalidArgumentException;

final readonly class CreateOutboundFromSchemeUseCase
{
    public function __construct(
        private RawOutboundParserPort $rawOutboundParserPort
    )
    {
    }

    /**
     * Create outbound entity from scheme string
     *
     * @param string $rawSchemeString Scheme string
     * @return Outbound Outbound entity
     *
     * @throws UnableToParseRawSchemeStringException If error while parsing scheme string
     * @throws UnsupportedProtocolException If scheme contains unsupported protocol
     * @throws UnsupportedSecurityException If scheme contains unsupported security type
     * @throws UnsupportedTransportException If scheme contains unsupported transport type
     * @throws InvalidArgumentException If scheme contains invalid fields
     */
    public function handle(string $rawSchemeString): Outbound
    {
        return OutboundFactory::fromRawOutbound(
            $this->rawOutboundParserPort->parse($rawSchemeString)
        );
    }
}