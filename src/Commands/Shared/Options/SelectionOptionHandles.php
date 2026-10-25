<?php

declare(strict_types=1);

namespace App\Commands\Shared\Options;

use Iva\Input\Option;

/**
 * The typed handles for one prefix-variant of the selection option block
 * (see SelectionOptionsTrait).
 *
 * Iva reads an option's value back through the very Option instance
 * addOption() returned, never by string name, so a prefixed copy of the
 * block needs its own bag of handles: one instance per prefix, built once in
 * configure() and kept around for resolve() to read through.
 */
final readonly class SelectionOptionHandles
{
    public function __construct(
        public Option $select,
        public Option $exclude,
    )
    {
    }
}
