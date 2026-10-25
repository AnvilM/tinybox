<?php

declare(strict_types=1);

namespace App\Application\Outbound\Selection\Syntax;

use App\Application\Outbound\Selection\Exception\SyntaxException;

/**
 * Turns a selection expression into tokens on demand.
 *
 * ## Why there is no token buffer
 *
 * What a piece of text means depends on where it sits in the grammar:
 * `RU-*` is a value after an operator, but would be a syntax error in a
 * structural position. So the parser asks for exactly what it expects:
 *
 *  - peek()/next() read the next *structural* token (identifier, operator,
 *    `&`, `|`, `!`, parentheses, comma, end of input);
 *  - readValue() reads a value (bare or quoted) and is called right after
 *    an operator or a comma.
 *
 * peek() never moves the cursor and nothing is pre-read, so switching between
 * the two modes can never see stale tokens.
 *
 * All offsets are character offsets (the source is split by UTF-8
 * characters), which keeps error carets aligned for non-ASCII input.
 */
final class Lexer
{
    /**
     * Characters that end a bare value (besides whitespace).
     */
    private const VALUE_TERMINATORS = [',', '&', '|', '(', ')', "'", '"'];

    /**
     * @var list<string>
     */
    private array $chars;

    private int $offset = 0;

    /**
     * @throws SyntaxException
     */
    public function __construct(
        private readonly string $source,
    )
    {
        $chars = preg_split('//u', $source, -1, PREG_SPLIT_NO_EMPTY);

        if ($chars === false) {
            throw new SyntaxException('Expression is not valid UTF-8', $source, 0);
        }

        $this->chars = $chars;
    }

    public function source(): string
    {
        return $this->source;
    }

    /**
     * Next structural token, without moving the cursor.
     *
     * @throws SyntaxException
     */
    public function peek(): Token
    {
        $offset = $this->offset;

        return $this->scan($offset);
    }

    /**
     * Next structural token, moving the cursor past it.
     *
     * @throws SyntaxException
     */
    public function next(): Token
    {
        return $this->scan($this->offset);
    }

    /**
     * Reads a value: bare (`vless`, `RU-*`) or quoted (`'my server'`).
     *
     * @throws SyntaxException
     */
    public function readValue(): Token
    {
        $this->skipWhitespace($this->offset);

        $start = $this->offset;
        $char = $this->chars[$start] ?? null;

        if ($char === "'" || $char === '"') {
            return $this->readQuotedValue($char);
        }

        $length = count($this->chars);
        $end = $start;

        while ($end < $length && !$this->isValueTerminator($this->chars[$end])) {
            $end++;
        }

        if ($end === $start) {
            throw new SyntaxException(sprintf('Expected value at position %d', $start), $this->source, $start);
        }

        $this->offset = $end;

        return new Token(TokenType::Value, implode('', array_slice($this->chars, $start, $end - $start)), $start);
    }

    /**
     * @throws SyntaxException
     */
    private function scan(int &$offset): Token
    {
        $this->skipWhitespace($offset);

        $start = $offset;
        $char = $this->chars[$start] ?? null;

        if ($char === null) {
            return new Token(TokenType::Eof, '', $start);
        }

        /**
         * `!=` and `!~` are operators, a lone `!` is a negation
         */
        if ($char === '!') {
            $following = $this->chars[$start + 1] ?? null;

            if ($following === '=') {
                $offset += 2;

                return new Token(TokenType::NotEquals, '!=', $start);
            }

            if ($following === '~') {
                $offset += 2;

                return new Token(TokenType::NotGlob, '!~', $start);
            }

            $offset++;

            return new Token(TokenType::Not, '!', $start);
        }

        $simple = match ($char) {
            '=' => TokenType::Equals,
            '~' => TokenType::Glob,
            '&' => TokenType::And,
            '|' => TokenType::Or,
            '(' => TokenType::LParen,
            ')' => TokenType::RParen,
            ',' => TokenType::Comma,
            default => null,
        };

        if ($simple !== null) {
            $offset++;

            return new Token($simple, $char, $start);
        }

        if ($this->isIdentStart($char)) {
            $length = count($this->chars);
            $end = $start + 1;

            while ($end < $length && $this->isIdentPart($this->chars[$end])) {
                $end++;
            }

            $offset = $end;

            return new Token(TokenType::Ident, implode('', array_slice($this->chars, $start, $end - $start)), $start);
        }

        throw new SyntaxException(sprintf("Unexpected token '%s' at position %d", $char, $start), $this->source, $start);
    }

    /**
     * Quoted value: `'...'` or `"..."`, escapes `\'`, `\"` and `\\` are allowed.
     *
     * @throws SyntaxException
     */
    private function readQuotedValue(string $quote): Token
    {
        $start = $this->offset;
        $length = count($this->chars);
        $offset = $start + 1;
        $value = '';

        while ($offset < $length) {
            $char = $this->chars[$offset];

            if ($char === $quote) {
                if ($value === '') {
                    throw new SyntaxException(sprintf('Value must not be empty at position %d', $start), $this->source, $start);
                }

                $this->offset = $offset + 1;

                return new Token(TokenType::Value, $value, $start);
            }

            if ($char === '\\') {
                $escaped = $this->chars[$offset + 1] ?? null;

                if ($escaped === null) {
                    break;
                }

                if ($escaped !== "'" && $escaped !== '"' && $escaped !== '\\') {
                    throw new SyntaxException(
                        sprintf("Invalid escape sequence '\\%s' at position %d. Allowed: \\', \\\", \\\\", $escaped, $offset),
                        $this->source,
                        $offset,
                    );
                }

                $value .= $escaped;
                $offset += 2;

                continue;
            }

            $value .= $char;
            $offset++;
        }

        throw new SyntaxException(sprintf('Unterminated quoted value starting at position %d', $start), $this->source, $start);
    }

    private function skipWhitespace(int &$offset): void
    {
        $length = count($this->chars);

        while ($offset < $length && $this->isWhitespace($this->chars[$offset])) {
            $offset++;
        }
    }

    private function isWhitespace(string $char): bool
    {
        return $char === ' ' || $char === "\t" || $char === "\n" || $char === "\r" || $char === "\v" || $char === "\f";
    }

    private function isValueTerminator(string $char): bool
    {
        return $this->isWhitespace($char) || in_array($char, self::VALUE_TERMINATORS, true);
    }

    private function isIdentStart(string $char): bool
    {
        return preg_match('/\A[A-Za-z_]\z/', $char) === 1;
    }

    private function isIdentPart(string $char): bool
    {
        return preg_match('/\A[A-Za-z0-9_]\z/', $char) === 1;
    }
}
