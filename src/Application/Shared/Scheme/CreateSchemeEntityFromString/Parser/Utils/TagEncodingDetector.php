<?php

declare(strict_types=1);

namespace App\Application\Shared\Scheme\CreateSchemeEntityFromString\Parser\Utils;

final readonly class TagEncodingDetector
{
    /**
     * Detects encoding type of given string.
     *
     * @param string $input Raw input string
     *
     * @return TagEncodingType Detected encoding type
     */
    public static function detect(string $input): TagEncodingType
    {
        /**
         * Normalize input by trimming whitespace.
         */
        $input = trim($input);

        /**
         * Empty string is treated as plain text.
         */
        if ($input === '') {
            return TagEncodingType::PLAIN_TEXT;
        }

        /**
         * Check if string is valid Base64.
         */
        if (self::isBase64($input)) {
            return TagEncodingType::BASE64;
        }

        /**
         * Check if string is URL encoded.
         */
        if (self::isUrlEncoded($input)) {
            return TagEncodingType::URL_ENCODED;
        }

        /**
         * Fallback to plain text.
         */
        return TagEncodingType::PLAIN_TEXT;
    }

    /**
     * Checks whether string is valid Base64.
     *
     * @param string $input Input string
     *
     * @return bool True if Base64, false otherwise
     */
    private static function isBase64(string $input): bool
    {
        /**
         * Reject if length is not multiple of 4.
         */
        if (strlen($input) % 4 !== 0) {
            return false;
        }

        /**
         * Validate allowed Base64 character set.
         */
        if (!preg_match('/^[A-Za-z0-9+\/]+={0,2}$/', $input)) {
            return false;
        }

        /**
         * Attempt strict Base64 decoding.
         */
        $decoded = base64_decode($input, true);

        /**
         * Reject if decoding fails.
         */
        if ($decoded === false) {
            return false;
        }

        /**
         * Verify reversibility (encode after decode).
         */
        if (base64_encode($decoded) !== $input) {
            return false;
        }

        /**
         * Ensure decoded content is mostly printable.
         */
        return self::isMostlyPrintable($decoded);
    }

    /**
     * Checks if string is mostly printable ASCII.
     *
     * @param string $str Input string
     *
     * @return bool True if mostly printable, false otherwise
     */
    private static function isMostlyPrintable(string $str): bool
    {
        /**
         * Get string length.
         */
        $len = strlen($str);

        /**
         * Reject empty decoded content.
         */
        if ($len === 0) {
            return false;
        }

        /**
         * Counter for printable characters.
         */
        $printable = 0;

        /**
         * Iterate through each byte.
         */
        for ($i = 0; $i < $len; $i++) {
            $ord = ord($str[$i]);

            /**
             * Check for printable ASCII or whitespace control chars.
             */
            if (
                ($ord >= 32 && $ord <= 126) || // printable ASCII
                $ord === 9 ||  // tab
                $ord === 10 || // newline
                $ord === 13    // carriage return
            ) {
                $printable++;
            }
        }

        /**
         * Return true if ratio exceeds threshold.
         */
        return ($printable / $len) > 0.85;
    }

    /**
     * Checks whether string is URL encoded.
     *
     * @param string $input Input string
     *
     * @return bool True if URL encoded, false otherwise
     */
    private static function isUrlEncoded(string $input): bool
    {
        /**
         * Quick reject if no encoding indicators present.
         */
        if (!str_contains($input, '%') && !str_contains($input, '+')) {
            return false;
        }

        /**
         * Decode URL-encoded string.
         */
        $decoded = urldecode($input);

        /**
         * Reject if decoding produces no change.
         */
        if ($decoded === $input) {
            return false;
        }

        /**
         * Ensure presence of valid %XX patterns.
         */
        if (!preg_match('/%[0-9A-Fa-f]{2}/', $input)) {
            return false;
        }

        /**
         * Accept as URL encoded.
         */
        return true;
    }
}