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
    private const int MIN_PORT = 1;
    private const int MAX_PORT = 65535;

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
        $rawSchemeString = trim($rawSchemeString);

        if ($rawSchemeString === '') {
            throw new UnableToParseRawSchemeStringException('Invalid raw scheme string');
        }

        $parsed = parse_url($rawSchemeString);

        if ($parsed === false) {
            throw new UnableToParseRawSchemeStringException("Unable to parse URL: $rawSchemeString");
        }

        $queryParams = [];
        if (isset($parsed['query']) && is_string($parsed['query'])) {
            parse_str($parsed['query'], $queryParams);
        }

        return new RawOutboundVO(
            protocol: $this->sanitizeString($parsed['scheme'] ?? null),
            tag: $this->decodeTag($parsed['fragment'] ?? null),
            uuid: $this->sanitizeString($parsed['user'] ?? null),
            server: $this->sanitizeString($parsed['host'] ?? null),
            server_port: $this->sanitizePort($parsed['port'] ?? null),
            sni: $this->sanitizeQueryParam($queryParams, 'sni'),
            pbk: $this->sanitizeQueryParam($queryParams, 'pbk'),
            sid: $this->sanitizeQueryParam($queryParams, 'sid'),
            flow: $this->sanitizeQueryParam($queryParams, 'flow'),
            fp: $this->sanitizeQueryParam($queryParams, 'fp'),
            transportType: $this->sanitizeQueryParam($queryParams, 'type'),
            shadowsocksPlugin: $this->sanitizeQueryParam($queryParams, 'plugin'),
            security: $this->sanitizeQueryParam($queryParams, 'security'),
            path: $this->sanitizeQueryParam($queryParams, 'path'),
            host: $this->sanitizeQueryParam($queryParams, 'host'),
            spx: $this->sanitizeQueryParam($queryParams, 'spx'),
            mode: $this->sanitizeQueryParam($queryParams, 'mode'),
            extra: $this->sanitizeQueryParam($queryParams, 'extra'),
            alpn: $this->sanitizeQueryParam($queryParams, 'alpn'),
        );
    }

    /**
     * Приводит произвольное значение к непустой строке либо null.
     */
    private function sanitizeString(mixed $value): ?string
    {
        if (!is_string($value)) {
            return null;
        }

        $value = trim($value);

        return $value === '' ? null : $value;
    }

    private function decodeTag(mixed $tag): ?string
    {
        $tag = $this->sanitizeString($tag);

        if ($tag === null) {
            return null;
        }

        $decoded = match (TagEncodingDetector::detect($tag)) {
            TagEncodingType::BASE64 => base64_decode($tag, true),
            TagEncodingType::URL_ENCODED => urldecode($tag),
            default => $tag,
        };

        // base64_decode со strict=true вернёт false на некорректных данных
        if ($decoded === false) {
            return null;
        }

        return $this->sanitizeString($decoded);
    }

    private function sanitizePort(mixed $port): ?int
    {
        if ($port === null || $port === '') {
            return null;
        }

        if (!is_numeric($port)) {
            return null;
        }

        $port = (int)$port;

        if ($port < self::MIN_PORT || $port > self::MAX_PORT) {
            return null;
        }

        return $port;
    }

    /**
     * Достаёт параметр из query-массива и гарантирует, что это либо
     * непустая строка, либо null (даже если пришёл массив/что угодно ещё).
     */
    private function sanitizeQueryParam(array $queryParams, string $key): ?string
    {
        return $this->sanitizeString($queryParams[$key] ?? null);
    }
}