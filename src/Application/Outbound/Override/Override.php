<?php

declare(strict_types=1);

namespace App\Application\Outbound\Override;

use App\Application\Outbound\Exception\Override\InvalidOverrideValueException;
use App\Domain\Outbound\Collection\OutboundMap;
use App\Domain\Shared\VO\Shared\NonEmptyStringVO;
use InvalidArgumentException;

/**
 * @template TType of OverrideType
 */
abstract readonly class Override
{
    /**
     * @return class-string<TType> The only OverrideType this Override can handle
     */
    abstract public function getTypeClass(): string;

    /**
     * Lower-case name used in the CLI, e.g. 'vless-uuid'.
     */
    abstract public function name(): string;

    /**
     * Creates override parameters from the raw VALUE of the CLI rule.
     *
     * @param string|null $value null - VALUE was not given
     *
     * @throws InvalidOverrideValueException
     */
    abstract public function createType(?string $value): OverrideType;

    /**
     * Entry point of an override: checks the type and proxies the call to {@see self::apply()}.
     *
     * @template T of OutboundMap
     *
     * @param T $outboundMap Outbounds to override
     * @param OverrideType $type Override parameters, must be an instance of {@see self::getTypeClass()}
     *
     * @return T Outbounds map with overridden outbounds
     *
     * @throws InvalidArgumentException If the type does not match this Override
     */
    final public function override(OutboundMap $outboundMap, OverrideType $type): OutboundMap
    {
        $typeClass = $this->getTypeClass();

        if (!($type instanceof $typeClass)) {
            throw new InvalidArgumentException(
                sprintf('%s expects %s, got %s.', static::class, $typeClass, $type::class)
            );
        }

        return $this->apply($outboundMap, $type);
    }

    /**
     * Override logic. Always receives a type that matches {@see self::getTypeClass()}.
     *
     * @template T of OutboundMap
     *
     * @param T $outboundMap Outbounds to override
     * @param TType $type Override parameters
     *
     * @return T Outbounds map with overridden outbounds
     */
    abstract protected function apply(OutboundMap $outboundMap, OverrideType $type): OutboundMap;

    /**
     * @throws InvalidOverrideValueException If the value is not given or is blank
     */
    protected function requireValue(?string $value): NonEmptyStringVO
    {
        if ($value === null || trim($value) === '') {
            throw new InvalidOverrideValueException(
                $this->name(),
                sprintf("a non-empty value is required, e.g. '%s=VALUE:EXPRESSION'", $this->name()),
            );
        }

        return new NonEmptyStringVO($value);
    }

    /**
     * @throws InvalidOverrideValueException If the value is given
     */
    protected function rejectValue(?string $value): void
    {
        if ($value !== null) {
            throw new InvalidOverrideValueException($this->name(), 'the override takes no value');
        }
    }

    /**
     * @return bool true if the value is not given or is 'true' (case-insensitive), false for 'false'
     *
     * @throws InvalidOverrideValueException If the value is anything else
     */
    protected function parseBool(?string $value): bool
    {
        if ($value === null) {
            return true;
        }

        return match (strtolower($value)) {
            'true' => true,
            'false' => false,
            default => throw new InvalidOverrideValueException(
                $this->name(),
                sprintf("expected 'true' or 'false', got '%s'", $value),
            ),
        };
    }
}
