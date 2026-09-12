<?php

declare(strict_types=1);

namespace App\Infrastructure\Outbound\Parser\RawOutboundParser;

use App\Application\Outbound\Exception\UnableToParseRawSchemeStringException;
use App\Application\Outbound\Ports\Parser\RawOutboundParserPort;
use App\Domain\Outbound\VO\RawOutboundVO;
use App\Infrastructure\Outbound\Parser\RawOutboundParser\Utils\TagEncodingDetector;
use App\Infrastructure\Outbound\Parser\RawOutboundParser\Utils\TagEncodingType;


final readonly class RawOutboundParser implements RawOutboundParserPort
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
    public function parse(string $rawSchemeString): RawOutboundVO
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

        return new RawOutboundVO(
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
            $queryParams['plugin'] ?? null,
            $queryParams['security'] ?? null,
            $queryParams['path'] ?? null,
            $queryParams['host'] ?? null,
            $queryParams['spx'] ?? null,
            $queryParams['mode'] ?? null,
            $queryParams['extra'] ?? null,
            $queryParams['alpn'] ?? null,
        );

    }


}