<?php

declare(strict_types=1);

namespace App\Application\Outbound\Spec;

final readonly class QuotedStringReader
{
    /**
     * Reads a quoted string; $text[$offset] must be the opening quote (' or ").
     * Escapes: \' \" \\ . Any other escape is an error.
     *
     * @return array{0: string, 1: int}|null [value, offset right after the closing quote];
     *                                       null if the quote is not closed or an escape is invalid
     */
    public static function read(string $text, int $offset): ?array
    {
        $quote = $text[$offset] ?? '';

        if ($quote !== "'" && $quote !== '"') {
            return null;
        }

        $length = strlen($text);
        $offset++;
        $value = '';

        while ($offset < $length) {
            $char = $text[$offset];

            if ($char === $quote) {
                return [$value, $offset + 1];
            }

            if ($char === '\\') {
                $escaped = $text[$offset + 1] ?? null;

                if ($escaped !== "'" && $escaped !== '"' && $escaped !== '\\') {
                    return null;
                }

                $value .= $escaped;
                $offset += 2;

                continue;
            }

            $value .= $char;
            $offset++;
        }

        return null;
    }
}
