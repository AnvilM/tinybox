<?php

declare(strict_types=1);

namespace App\Application\Outbound\Override;

use App\Application\Outbound\Exception\Override\InvalidOverrideSpecException;
use App\Application\Outbound\Exception\Override\InvalidOverrideValueException;
use App\Application\Outbound\Exception\Override\UnknownOverrideException;
use App\Application\Outbound\Selection\Exception\SelectionException;
use App\Application\Outbound\Selection\Syntax\SelectorParser;
use App\Application\Outbound\Spec\QuotedStringReader;

/**
 * Parses override SPEC string: ACTION[=VALUE]:EXPRESSION
 *
 * ACTION is the override name (case-insensitive). If the first non-space character of VALUE is a quote
 * (' or "), VALUE is the quoted string (escapes: \', \", \\), then optional spaces and a required ':'.
 * Otherwise VALUE is everything up to the first ':'. The expression may contain colons.
 * Empty expression - the whole current set.
 *
 * Stateless, safe to share.
 */
final readonly class OverrideSpecParser
{
    public function __construct(
        private SelectorParser   $selectorParser,
        private OverrideRegistry $registry,
    )
    {
    }

    /**
     * @throws InvalidOverrideSpecException If SPEC has no name, no ':', the quote is not closed
     *                                      or there is something between the closing quote and ':'
     * @throws UnknownOverrideException
     * @throws InvalidOverrideValueException
     * @throws SelectionException If the expression is invalid
     */
    public function parse(string $spec): OverrideDefinition
    {
        $text = ltrim($spec);

        if (preg_match('/^[A-Za-z][A-Za-z0-9-]*/', $text, $matches) !== 1) {
            throw new InvalidOverrideSpecException($spec);
        }

        $name = strtolower($matches[0]);
        $rest = ltrim(substr($text, strlen($matches[0])));
        $value = null;

        if (str_starts_with($rest, '=')) {
            $rest = ltrim(substr($rest, 1));
            $first = $rest[0] ?? '';

            if ($first === "'" || $first === '"') {
                $read = QuotedStringReader::read($rest, 0);

                if ($read === null) {
                    throw new InvalidOverrideSpecException($spec);
                }

                [$value, $endOffset] = $read;
                $rest = ltrim(substr($rest, $endOffset));
            } else {
                $separatorPosition = strpos($rest, ':');

                if ($separatorPosition === false) {
                    throw new InvalidOverrideSpecException($spec);
                }

                $value = trim(substr($rest, 0, $separatorPosition));
                $rest = substr($rest, $separatorPosition);
            }
        }

        if (!str_starts_with($rest, ':')) {
            throw new InvalidOverrideSpecException($spec);
        }

        $expression = trim(substr($rest, 1));

        $override = $this->registry->byName($name, $spec);

        try {
            $type = $override->createType($value);
        } catch (InvalidOverrideValueException $exception) {
            throw new InvalidOverrideValueException($exception->overrideName, $exception->reason, $spec);
        }

        return new OverrideDefinition(
            $type,
            $expression === '' ? null : $this->selectorParser->parse($expression),
            $spec,
        );
    }
}
