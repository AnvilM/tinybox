<?php

declare(strict_types=1);

namespace App\Application\Outbound\Override;

use App\Application\Outbound\Pipeline\PipelineStep;
use App\Application\Outbound\Selection\Node\SelectorNode;

/**
 * What the user asked for: parsed CLI data, before any selection happened.
 */
final readonly class OverrideDefinition implements PipelineStep
{
    /**
     * @param OverrideType $type Override parameters, already validated
     * @param SelectorNode|null $selector Selection tree, null - the whole current set
     * @param string $source Original SPEC string, used in error messages
     */
    public function __construct(
        public OverrideType  $type,
        public ?SelectorNode $selector,
        public string        $source,
    )
    {
    }
}
