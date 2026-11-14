<?php

declare(strict_types=1);

namespace App\Domain\Shared\VO\Config\Common\Http;

final readonly class HttpCommonConfigVO
{
    /**
     * @param bool $followRedirects Follow http redirects (3xx responses with Location header)
     * @param bool $acceptGzip Send "Accept-Encoding: gzip" header. Gzip encoded responses are decoded regardless of this option
     */
    public function __construct(
        public bool $followRedirects,
        public bool $acceptGzip,
    )
    {
    }
}
