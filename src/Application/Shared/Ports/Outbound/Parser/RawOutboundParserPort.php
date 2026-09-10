<?php

declare(strict_types=1);

namespace App\Application\Shared\Ports\Outbound\Parser;

use App\Application\Exception\Shared\Scheme\CreateSchemeEntityFromString\UnableToParseRawSchemeStringException;
use App\Domain\Outbound\DTO\RawOutboundDTO;

interface RawOutboundParserPort
{
    /**
     * Parses raw scheme string into raw outbound dto
     *
     * @param string $rawSchemeString Scheme string e.g., vless://uuid@host:port?...
     *
     * @return RawOutboundDTO Raw outbound dto
     *
     * @throws UnableToParseRawSchemeStringException Throws if unable to parse scheme
     */
    public function parse(string $rawSchemeString): RawOutboundDTO;
}