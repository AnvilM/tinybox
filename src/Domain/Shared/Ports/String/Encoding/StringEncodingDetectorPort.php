<?php

declare(strict_types=1);

namespace App\Domain\Shared\Ports\String\Encoding;

interface StringEncodingDetectorPort
{
    /**
     * Checks whether string is valid Base64.
     *
     * @param string $input Input string
     *
     * @return bool True if Base64, false otherwise
     */
    public function isBase64(string $input): bool;


    /**
     * Checks whether string is URL encoded.
     *
     * @param string $input Input string
     *
     * @return bool True if URL encoded, false otherwise
     */
    public function isUrlEncoded(string $input): bool;
}