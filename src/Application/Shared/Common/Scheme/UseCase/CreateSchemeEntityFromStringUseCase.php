<?php

declare(strict_types=1);

namespace App\Application\Shared\Common\Scheme\UseCase;

use App\Application\Shared\Common\Scheme\Parser\RawSchemeParser;
use App\Application\Shared\Scheme\Exception\Shared\Parser\UnableToParseRawSchemeStringException;
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
     * @throws UnsupportedSchemeType If scheme type is not supported
     * @throws InvalidArgumentException If scheme string miss required fields
     */
    public function handle(string $rawSchemeString): Scheme
    {
        return SchemeFactory::fromRawSchemeVO(
            $this->rawSchemeParser->parse($rawSchemeString),
        );
    }
}