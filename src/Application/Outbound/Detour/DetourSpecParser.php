<?php

declare(strict_types=1);

namespace App\Application\Outbound\Detour;

use App\Application\Outbound\Exception\Detour\InvalidDetourSpecException;
use App\Application\Outbound\Selection\Exception\SelectionException;
use App\Application\Outbound\Selection\Syntax\SelectorParser;

/**
 * Parses detour SPEC string: DETOUR_TAG:EXPRESSION
 *
 * If the first non-space character is a quote (' or "), the tag is the quoted string
 * (escapes: \', \", \\), then optional spaces and a required ':'.
 * Otherwise the string is split by the first ':', so the expression may contain colons.
 * Empty tag is an error, empty expression - all outbounds.
 *
 * Stateless, safe to share.
 */
final readonly class DetourSpecParser
{
    public function __construct(
        private SelectorParser $selectorParser,
    )
    {
    }

    /**
     * @throws InvalidDetourSpecException If SPEC has no ':', the tag is empty, the quote is not closed
     *                                    or there is something between the closing quote and ':'
     * @throws SelectionException If the expression is invalid
     */
    public function parse(string $spec): DetourDefinition
    {
        $trimmed = ltrim($spec);
        $first = $trimmed[0] ?? '';

        if ($first === "'" || $first === '"') {
            [$tag, $expression] = $this->splitQuoted($spec, $trimmed, $first);
        } else {
            $separatorPosition = strpos($spec, ':');

            if ($separatorPosition === false) {
                throw new InvalidDetourSpecException($spec);
            }

            $tag = trim(substr($spec, 0, $separatorPosition));
            $expression = substr($spec, $separatorPosition + 1);
        }

        if ($tag === '') {
            throw new InvalidDetourSpecException($spec);
        }

        $expression = trim($expression);

        return new DetourDefinition(
            $tag,
            $expression === '' ? null : $this->selectorParser->parse($expression),
            $spec,
        );
    }

    /**
     * @return array{0: string, 1: string} Tag and the rest of the string after ':'
     *
     * @throws InvalidDetourSpecException
     */
    private function splitQuoted(string $spec, string $trimmed, string $quote): array
    {
        $length = strlen($trimmed);
        $offset = 1;
        $tag = '';
        $closed = false;

        while ($offset < $length) {
            $char = $trimmed[$offset];

            if ($char === $quote) {
                $closed = true;
                $offset++;
                break;
            }

            if ($char === '\\') {
                $escaped = $trimmed[$offset + 1] ?? null;

                if ($escaped !== "'" && $escaped !== '"' && $escaped !== '\\') {
                    throw new InvalidDetourSpecException($spec);
                }

                $tag .= $escaped;
                $offset += 2;

                continue;
            }

            $tag .= $char;
            $offset++;
        }

        if (!$closed) {
            throw new InvalidDetourSpecException($spec);
        }

        $rest = ltrim(substr($trimmed, $offset));

        if (!str_starts_with($rest, ':')) {
            throw new InvalidDetourSpecException($spec);
        }

        return [$tag, substr($rest, 1)];
    }
}
