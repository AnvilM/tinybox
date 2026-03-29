<?php

declare(strict_types=1);

namespace App\Application\Shared\Shared\Shared\Scheme\Shared\UseCase\CreateSchemeEntityFromString;

use App\Application\Shared\Scheme\Exception\Shared\Parser\UnableToParseRawSchemeStringException;
use App\Application\Shared\Shared\Shared\Scheme\Shared\Shared\Parser\RawSchemeParser;
use App\Domain\Scheme\Entity\Scheme;
use App\Domain\Scheme\Exception\UnsupportedSchemeType;
use App\Domain\Scheme\Factory\SchemeFactory;
use InvalidArgumentException;

final readonly class CreateSchemeEntityFromStringUseCase
{
    public function __construct(
        private RawSchemeParser $rawSchemeParser,
    )
    {
    }

    /**
     * Create scheme entity from scheme string e.g. 'vless://uuid@host:port?...'
     *
     * @param string $rawSchemeString Scheme string
     *
     * @return Scheme Scheme entity
     *
     * @throws UnableToParseRawSchemeStringException If unable to parse scheme string, e.g. invalid format
     * @throws InvalidArgumentException If scheme string miss required fields
     * @throws UnsupportedSchemeType If scheme type is unsupported
     */
    public function handle(string $rawSchemeString): Scheme
    {
        return SchemeFactory::fromRawSchemeVO(
            $this->rawSchemeParser->parse($rawSchemeString),
        );
    }
}