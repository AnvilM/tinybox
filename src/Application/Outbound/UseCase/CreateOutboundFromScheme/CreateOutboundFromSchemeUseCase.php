<?php

declare(strict_types=1);

namespace App\Application\Outbound\UseCase\CreateOutboundFromScheme;

use App\Application\Outbound\Exception\UnableToParseRawSchemeStringException;
use App\Application\Outbound\Ports\Parser\RawOutboundParserPort;
use App\Domain\Outbound\Entity\Outbound;
use App\Domain\Outbound\Exception\UnsupportedProtocolException;
use App\Domain\Outbound\Exception\UnsupportedSecurityException;
use App\Domain\Outbound\Exception\UnsupportedTransportException;
use App\Domain\Outbound\Factory\OutboundFactory;
use InvalidArgumentException;

final readonly class CreateOutboundFromSchemeUseCase
{
    public function __construct(
        private RawOutboundParserPort $rawOutboundParserPort,
        private OutboundFactory       $outboundFactory
    )
    {
    }

    /**
     * Create outbound entity from scheme string
     *
     * @param string $rawSchemeString Scheme string
     * @param string|null $id Outbound id null for auto generate
     *
     * @return Outbound Outbound entity
     *
     * @throws UnableToParseRawSchemeStringException If error while parsing scheme string
     * @throws UnsupportedProtocolException If scheme contains unsupported protocol
     * @throws UnsupportedSecurityException If scheme contains unsupported security type
     * @throws UnsupportedTransportException If scheme contains unsupported transport type
     * @throws InvalidArgumentException If scheme contains invalid fields
     */
    public function handle(string $rawSchemeString, ?string $id = null): Outbound
    {
        return $this->outboundFactory->fromRawOutbound(
            $this->rawOutboundParserPort->parse($rawSchemeString), $id
        );
    }
}