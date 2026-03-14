<?php

declare(strict_types=1);

namespace App\Application\Shared\Scheme\Shared\Parser;

use App\Application\Shared\Scheme\Exception\UnableToParseRawSchemeStringException;
use App\Domain\Scheme\VO\RawSchemeVO;


final readonly class RawSchemeParser
{
    /**
     * Parses raw schemes string into array of rawSchemeVO
     *
     * @param string $rawSchemeString Scheme string e.g., vless://uuid@host:port?...
     *
     * @return RawSchemeVO Parsed rawSchemeVO
     *
     * @throws UnableToParseRawSchemeStringException Throws if unable to parse scheme
     */
    public function parse(string $rawSchemeString): RawSchemeVO
    {


        $parsed = parse_url($rawSchemeString);
        if (!$parsed) {
            throw new UnableToParseRawSchemeStringException("Unable to parse URL: $rawSchemeString");
        }

        $queryParams = [];
        if (isset($parsed['query'])) {
            parse_str($parsed['query'], $queryParams);
        }

        return new RawSchemeVO(
            $parsed['scheme'] ?? null,
            $parsed['fragment'] ?? null,
            $parsed['user'] ?? null,
            $parsed['host'] ?? null,
            (int)$parsed['port'] ?? null,
            $queryParams['sni'] ?? null,
            $queryParams['pbk'] ?? null,
            $queryParams['sid'] ?? null,
            $queryParams['flow'] ?? null,
            $queryParams['fp'] ?? null,
        );

    }
}