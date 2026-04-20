<?php

declare(strict_types=1);

namespace App\Application\Outbound\UseCase\CreateOutboundFromRawSchemeString;

use App\Application\Exception\Shared\Scheme\CreateSchemeEntityFromString\UnableToParseRawSchemeStringException;
use App\Application\Shared\Scheme\CreateSchemeEntityFromString\Parser\RawSchemeParser;
use App\Domain\Outbound\Entity\Outbound;
use App\Domain\Outbound\Exception\UnsupportedOutboundTypeException;
use App\Domain\Outbound\Factory\FromScheme\FromSchemeOutboundFactory;
use App\Domain\Scheme\Exception\UnsupportedSchemeType;
use App\Domain\Scheme\Factory\SchemeFactory;
use App\Domain\Shared\Exception\CriticalException;
use InvalidArgumentException;

final readonly class CreateOutboundFromRawSchemeStringUseCase
{
    public function __construct(
        private RawSchemeParser $rawSchemeParser,
    )
    {
    }

    /**
     * @throws CriticalException
     */
    public function handle(string $rawSchemeString): Outbound
    {

        try {
            return FromSchemeOutboundFactory::fromScheme(
                SchemeFactory::fromRawSchemeVO(
                    $this->rawSchemeParser->parse($rawSchemeString)
                )
            );
        } catch (UnsupportedOutboundTypeException|UnsupportedSchemeType|UnableToParseRawSchemeStringException|InvalidArgumentException) {
            throw new CriticalException("Unable to create outbound");
            // TODO: add reporter event
        }
    }
}