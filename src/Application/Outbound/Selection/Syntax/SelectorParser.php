<?php

declare(strict_types=1);

namespace App\Application\Outbound\Selection\Syntax;

use App\Application\Outbound\Selection\Exception\SyntaxException;
use App\Application\Outbound\Selection\Node\AllOfNode;
use App\Application\Outbound\Selection\Node\AnyOfNode;
use App\Application\Outbound\Selection\Node\ConditionNode;
use App\Application\Outbound\Selection\Node\NotNode;
use App\Application\Outbound\Selection\Node\SelectorNode;

/**
 * Recursive descent parser for the selection language.
 *
 * ```
 * or     := and  ( '|' and )*
 * and    := unary ( '&' unary )*
 * unary  := '!' unary | '(' or ')' | cond
 * cond   := IDENT op value ( ',' value )*
 * op     := '=' | '!=' | '~' | '!~'
 * ```
 *
 * The parser only knows syntax. It does not check that a property, an
 * operator or a value exists - that is the compiler's job. It keeps no state
 * between calls (every parse() gets its own Lexer), so it is safe to share.
 *
 * `!=` and `!~` are sugar: they produce NotNode(ConditionNode) with the
 * positive operator, so the tree only ever contains `=` and `~` leaves.
 */
final readonly class SelectorParser
{
    /**
     * @throws SyntaxException
     */
    public function parse(string $expression): SelectorNode
    {
        $lexer = new Lexer($expression);

        if ($lexer->peek()->type === TokenType::Eof) {
            throw new SyntaxException('Expression is empty', $expression, 0);
        }

        $node = $this->parseOr($lexer);

        $token = $lexer->peek();

        if ($token->type !== TokenType::Eof) {
            throw $this->unexpected($token, $lexer);
        }

        return $node;
    }

    /**
     * @throws SyntaxException
     */
    private function parseOr(Lexer $lexer): SelectorNode
    {
        $nodes = [$this->parseAnd($lexer)];

        while ($lexer->peek()->type === TokenType::Or) {
            $lexer->next();
            $nodes[] = $this->parseAnd($lexer);
        }

        return count($nodes) === 1 ? $nodes[0] : new AnyOfNode($nodes);
    }

    /**
     * @throws SyntaxException
     */
    private function parseAnd(Lexer $lexer): SelectorNode
    {
        $nodes = [$this->parseUnary($lexer)];

        while ($lexer->peek()->type === TokenType::And) {
            $lexer->next();
            $nodes[] = $this->parseUnary($lexer);
        }

        return count($nodes) === 1 ? $nodes[0] : new AllOfNode($nodes);
    }

    /**
     * @throws SyntaxException
     */
    private function parseUnary(Lexer $lexer): SelectorNode
    {
        $token = $lexer->peek();

        switch ($token->type) {
            case TokenType::Not:
                $lexer->next();

                return new NotNode($this->parseUnary($lexer));

            case TokenType::LParen:
                $lexer->next();

                $node = $this->parseOr($lexer);

                $closing = $lexer->next();

                if ($closing->type !== TokenType::RParen) {
                    throw new SyntaxException(
                        $closing->type === TokenType::Eof
                            ? sprintf("Expected ')' but found end of expression at position %d", $closing->position)
                            : sprintf("Expected ')' but found '%s' at position %d", $closing->text, $closing->position),
                        $lexer->source(),
                        $closing->position,
                    );
                }

                return $node;

            case TokenType::Ident:
                return $this->parseCondition($lexer);

            default:
                throw $this->unexpected($token, $lexer);
        }
    }

    /**
     * @throws SyntaxException
     */
    private function parseCondition(Lexer $lexer): SelectorNode
    {
        $identifier = $lexer->next();
        $operator = $lexer->next();

        [$symbol, $negated] = match ($operator->type) {
            TokenType::Equals => ['=', false],
            TokenType::NotEquals => ['=', true],
            TokenType::Glob => ['~', false],
            TokenType::NotGlob => ['~', true],
            default => throw new SyntaxException(
                sprintf("Expected operator (=, !=, ~, !~) after property '%s'", $identifier->text),
                $lexer->source(),
                $operator->position,
            ),
        };

        $operands = [$lexer->readValue()->text];

        while ($lexer->peek()->type === TokenType::Comma) {
            $lexer->next();
            $operands[] = $lexer->readValue()->text;
        }

        $condition = new ConditionNode(
            strtolower($identifier->text),
            $symbol,
            $operands,
            $lexer->source(),
            $identifier->position,
        );

        return $negated ? new NotNode($condition) : $condition;
    }

    private function unexpected(Token $token, Lexer $lexer): SyntaxException
    {
        return new SyntaxException(
            $token->type === TokenType::Eof
                ? sprintf('Unexpected end of expression at position %d', $token->position)
                : sprintf("Unexpected token '%s' at position %d", $token->text, $token->position),
            $lexer->source(),
            $token->position,
        );
    }
}
