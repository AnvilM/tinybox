<?php

declare(strict_types=1);

namespace App\Application\Shared\Shared\Shared\Scheme\Shared\Shared\Parser;

use App\Application\Shared\Scheme\Exception\Shared\Parser\UnableToParseRawSchemeStringException;
use App\Application\Shared\Shared\Shared\Scheme\Shared\Shared\Parser\Utils\TagEncodingDetector;
use App\Application\Shared\Shared\Shared\Scheme\Shared\Shared\Parser\Utils\TagEncodingType;
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

        if (trim($rawSchemeString) === '') throw new UnableToParseRawSchemeStringException("Invalid raw scheme string");

        $parsed = parse_url($rawSchemeString);
        if (!$parsed) {
            throw new UnableToParseRawSchemeStringException("Unable to parse URL: $rawSchemeString");
        }

        $queryParams = [];
        if (isset($parsed['query'])) {
            parse_str($parsed['query'], $queryParams);
        }

        $tag = $parsed['fragment'] ?? null;

        if ($tag != null) {
            $tag = match (TagEncodingDetector::detect($tag)) {
                TagEncodingType::BASE64 => base64_decode($tag),
                TagEncodingType::URL_ENCODED => urldecode($tag),
                default => $tag
            };
        }

        return new RawSchemeVO(
            $parsed['scheme'] ?? null,
            $tag,
            $parsed['user'] ?? null,
            $parsed['host'] ?? null,
            (int)$parsed['port'] ?? null,
            $queryParams['sni'] ?? null,
            $queryParams['pbk'] ?? null,
            $queryParams['sid'] ?? null,
            $queryParams['flow'] ?? null,
            $queryParams['fp'] ?? null,
            $queryParams['type'] ?? null,
            $queryParams['plugin'] ?? null
        );

    }


}