<?php

declare(strict_types=1);

namespace App\Application\Urltest\Group;

use App\Application\Outbound\Selection\Node\SelectorNode;

/**
 * What the user asked for: parsed CLI data, before any selection happened.
 */
final readonly class UrltestGroupDefinition
{
    /**
     * @param string|null $tag Urltest tag, null - not specified by the user
     * @param SelectorNode|null $selector Selection tree, null - all outbounds
     * @param string $source Original SPEC string, used in error messages
     */
    public function __construct(
        public ?string       $tag,
        public ?SelectorNode $selector,
        public string        $source,
    )
    {
    }
}
