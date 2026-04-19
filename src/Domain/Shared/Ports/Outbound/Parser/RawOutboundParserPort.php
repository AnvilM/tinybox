<?php

declare(strict_types=1);

namespace App\Domain\Shared\Ports\Outbound\Parser;

use App\Domain\Outbound\Exception\UnsupportedOutboundTypeException;
use App\Domain\Outbound\VO\RawOutbound\RawOutboundVO;
use InvalidArgumentException;

interface RawOutboundParserPort
{
    /**
     * Parse outbound as JSON string to raw outbound value object
     *
     * @param array $rawOutbound Outbound as JSON string
     *
     * @return RawOutboundVO Raw outbound value object
     *
     * @throws UnsupportedOutboundTypeException If outbound type not supported
     * @throws InvalidArgumentException If outbound config is invalid
     */
    public function parse(array $rawOutbound): RawOutboundVO;
}