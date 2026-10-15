<?php

declare(strict_types=1);

namespace App\Application\Outbound\Override;

/**
 * Base class for override parameters.
 *
 * Every concrete Override has exactly one matching OverrideType
 * that carries the values the Override needs to do its work.
 */
abstract readonly class OverrideType
{
}
