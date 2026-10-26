<?php

declare(strict_types=1);

namespace App\Application\Outbound\Selection\Operator;

use LogicException;

/**
 * Central place that knows every comparison operator.
 *
 * The constructor lists concrete classes, so PHP-DI autowires it.
 *
 * To add a new operator:
 *  1. Create a class implementing `Operator` (Selection/Operator).
 *  2. Add it as a typed constructor parameter here and to the map below.
 *  3. If it needs a new symbol, add a token to TokenType/Lexer and a branch
 *     to SelectorParser::parseCondition().
 */
final readonly class OperatorRegistry
{
    /**
     * @var array<string, Operator>
     */
    private array $operators;

    public function __construct(
        EqualsOperator $equals,
        GlobOperator   $glob,
    )
    {
        $this->operators = [
            $equals->symbol() => $equals,
            $glob->symbol() => $glob,
        ];
    }

    /**
     * Symbols come only from the parser, so an unknown one is a programmer error.
     *
     * @throws LogicException
     */
    public function get(string $symbol): Operator
    {
        return $this->operators[$symbol]
            ?? throw new LogicException("Unknown selection operator '$symbol'");
    }
}
