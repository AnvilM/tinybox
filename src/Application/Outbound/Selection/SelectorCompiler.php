<?php

declare(strict_types=1);

namespace App\Application\Outbound\Selection;

use App\Application\Outbound\Selection\Exception\IncompatibleOperatorException;
use App\Application\Outbound\Selection\Exception\InvalidValueException;
use App\Application\Outbound\Selection\Exception\SelectionException;
use App\Application\Outbound\Selection\Node\AllOfNode;
use App\Application\Outbound\Selection\Node\AnyOfNode;
use App\Application\Outbound\Selection\Node\ConditionNode;
use App\Application\Outbound\Selection\Node\NotNode;
use App\Application\Outbound\Selection\Node\SelectorNode;
use App\Application\Outbound\Selection\Operator\OperatorRegistry;
use App\Application\Outbound\Selection\Property\PropertyRegistry;
use App\Application\Outbound\Selection\Specification\AllOfSpecification;
use App\Application\Outbound\Selection\Specification\AnyOfSpecification;
use App\Application\Outbound\Selection\Specification\ConditionSpecification;
use App\Application\Outbound\Selection\Specification\NotSpecification;
use App\Domain\Interface\Outbound\OutboundSpecificationInterface;
use InvalidArgumentException;
use LogicException;

/**
 * Turns a selector tree into an outbound specification.
 *
 * This is where semantics live: which properties and operators exist and
 * which values are valid.
 */
final readonly class SelectorCompiler
{
    public function __construct(
        private PropertyRegistry $properties,
        private OperatorRegistry $operators,
    )
    {
    }

    /**
     * @throws SelectionException
     */
    public function compile(SelectorNode $node): OutboundSpecificationInterface
    {
        return match (true) {
            $node instanceof ConditionNode => $this->compileCondition($node),
            $node instanceof AllOfNode => new AllOfSpecification($this->compileAll($node->nodes)),
            $node instanceof AnyOfNode => new AnyOfSpecification($this->compileAll($node->nodes)),
            $node instanceof NotNode => new NotSpecification($this->compile($node->node)),
            default => throw new LogicException('Unsupported selector node ' . $node::class),
        };
    }

    /**
     * @param list<SelectorNode> $nodes
     *
     * @return list<OutboundSpecificationInterface>
     *
     * @throws SelectionException
     */
    private function compileAll(array $nodes): array
    {
        return array_map(fn(SelectorNode $node): OutboundSpecificationInterface => $this->compile($node), $nodes);
    }

    /**
     * @throws SelectionException
     */
    private function compileCondition(ConditionNode $node): ConditionSpecification
    {
        $property = $this->properties->get($node->property, $node->source, $node->position);
        $operator = $this->operators->get($node->operator);

        if (!$operator->supports($property->kind())) {
            throw new IncompatibleOperatorException($operator->symbol(), $property->name(), $node->source, $node->position);
        }

        $operands = $node->operands;

        if ($operator->operandsAreValues()) {
            $operands = [];

            foreach ($node->operands as $raw) {
                try {
                    $operands[] = $property->parse($raw);
                } catch (InvalidArgumentException $e) {
                    throw new InvalidValueException(
                        $raw,
                        $property->name(),
                        $property->allowedValues(),
                        $e->getMessage(),
                        $node->source,
                        $node->position,
                    );
                }
            }
        }

        return new ConditionSpecification($property, $operator->matcher($operands));
    }
}
