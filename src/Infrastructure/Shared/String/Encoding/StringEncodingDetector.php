<?php

declare(strict_types=1);

namespace App\Infrastructure\Shared\String\Encoding;

use App\Domain\Shared\Ports\String\Encoding\StringEncodingDetectorPort;

final readonly class StringEncodingDetector implements StringEncodingDetectorPort
{

    /**
     * @inheritDoc
     */
    public function isUrlEncoded(string $input): bool
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

    /**
     * @inheritDoc
     */
    public function isBase64(string $input): bool
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
}