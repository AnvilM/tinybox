<?php

declare(strict_types=1);

namespace App\Application\Outbound\Detour;

use App\Application\Outbound\Pipeline\PipelineStep;
use App\Application\Outbound\Selection\Node\SelectorNode;

/**
 * What the user asked for: parsed CLI data, before any selection happened.
 */
final readonly class DetourDefinition implements PipelineStep
{
    /**
     * @param string $targetTag Tag of the detour outbound, not empty
     * @param SelectorNode|null $selector Selection tree of the outbounds to set the detour to, null - all outbounds
     * @param string $source Original SPEC string, used in error messages
     */
    public function __construct(
        public string        $targetTag,
        public ?SelectorNode $selector,
        public string        $source,
    )
    {
    }
}
