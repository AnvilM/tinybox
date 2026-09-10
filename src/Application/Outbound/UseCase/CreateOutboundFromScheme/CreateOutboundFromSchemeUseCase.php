<?php

declare(strict_types=1);

namespace App\Application\Outbound\UseCase\CreateOutboundFromScheme;

use App\Application\Exception\Shared\Scheme\CreateSchemeEntityFromString\UnableToParseRawSchemeStringException;
use App\Application\Shared\Ports\Outbound\Parser\RawOutboundParserPort;
use App\Domain\Outbound\Entity\Outbound;
use App\Domain\Outbound\Exception\UnsupportedProtocolException;
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
     * @throws UnableToParseRawSchemeStringException
     * @throws UnsupportedProtocolException
     * @throws InvalidArgumentException
     */
    public function handle(string $rawSchemeString): Outbound
    {
        return OutboundFactory::fromRawOutbound(
            $this->rawOutboundParserPort->parse($rawSchemeString)
        );
    }
}