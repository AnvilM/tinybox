<?php

declare(strict_types=1);

namespace App\Application\Urltest\Group;

use App\Application\Outbound\Selection\Exception\SelectionException;
use App\Application\Outbound\Selection\Syntax\SelectorParser;
use App\Application\Urltest\Exception\InvalidUrltestGroupException;

/**
 * Parses urltest group SPEC string: TAG:EXPRESSION
 *
 * The string is split by the first ':', so the expression may contain colons.
 * Empty tag - tag is not specified, empty expression - all outbounds.
 *
 * Stateless, safe to share.
 */
final readonly class UrltestGroupSpecParser
{
    public function __construct(
        private SelectorParser $selectorParser,
    )
    {
    }

    /**
     * @throws InvalidUrltestGroupException If SPEC has no ':'
     * @throws SelectionException If the expression is invalid
     */
    public function parse(string $spec): UrltestGroupDefinition
    {
        $separatorPosition = strpos($spec, ':');

        if ($separatorPosition === false) {
            throw new InvalidUrltestGroupException($spec);
        }

        $tag = trim(substr($spec, 0, $separatorPosition));
        $expression = trim(substr($spec, $separatorPosition + 1));

        return new UrltestGroupDefinition(
            $tag === '' ? null : $tag,
            $expression === '' ? null : $this->selectorParser->parse($expression),
            $spec,
        );
    }
}
