<?php

declare(strict_types=1);

namespace App\Application\Outbound\Override;

use App\Domain\Outbound\Collection\OutboundMap;
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
}
