<?php

declare(strict_types=1);

namespace App\Application\Outbound\Selection\Syntax;

/**
 * Kinds of tokens produced by Lexer.
 *
 * NOTE: `Value` is never returned by Lexer::peek()/next(); values are read
 * explicitly through Lexer::readValue(), because what counts as a value
 * depends on the position in the grammar, not on the characters alone.
 */
enum TokenType
{
    case Ident;
    case Equals;
    case NotEquals;
    case Glob;
    case NotGlob;
    case And;
    case Or;
    case Not;
    case LParen;
    case RParen;
    case Comma;
    case Value;
    case Eof;
}
