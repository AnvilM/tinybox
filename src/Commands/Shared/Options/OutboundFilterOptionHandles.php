<?php

declare(strict_types=1);

namespace App\Commands\Shared\Options;

use Iva\Input\Option;

/**
 * The typed handles for one prefix-variant of the outbound-filter option
 * block (see OutboundFilterOptionsTrait).
 *
 * Iva reads an option's value back through the very Option instance
 * addOption() returned — never by string name (see Iva\Input\Input's
 * docblock). The previous version resolved a "prefixed copy of the same
 * block" by re-building the option's string name (`$prefix.ucfirst($name)`)
 * and looking it up again at resolve() time; that trick relied entirely on
 * Symfony's stringly-typed `getOption(string $name)`. Here, a prefixed copy
 * of the block needs its own bag of typed handles instead — one instance of
 * this class per prefix, built once in configure() and kept around for
 * resolve() to read through.
 */
final readonly class OutboundFilterOptionHandles
{
    public function __construct(
        public Option $countryCode,
        public Option $countryCodeExcept,
        public Option $countryOnlyAvailable,
        public Option $excludeCountryCode,
        public Option $excludeCountryCodeExcept,
        public Option $excludeCountryOnlyAvailable,
        public Option $countryOutboundIpFallback,
        public Option $outboundType,
        public Option $outboundTypeExcept,
        public Option $excludeOutboundType,
        public Option $excludeOutboundTypeExcept,
        public Option $excludeOutbound,
        public Option $exceptOutbound,
    )
    {
    }
}
