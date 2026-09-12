<?php

declare(strict_types=1);

namespace App\Application\Outbound\Ports\Parser;

use App\Application\Outbound\Exception\UnableToParseRawSchemeStringException;
use App\Domain\Outbound\VO\RawOutboundVO;

interface RawOutboundParserPort
{
    /**
     * Parses raw scheme string into raw outbound dto
     *
     * @param string $rawSchemeString Scheme string e.g., vless://uuid@host:port?...
     *
     * @return RawOutboundVO Raw outbound dto
     *
     * @throws UnableToParseRawSchemeStringException Throws if unable to parse scheme
     */
    public function parse(string $rawSchemeString): RawOutboundVO;
}