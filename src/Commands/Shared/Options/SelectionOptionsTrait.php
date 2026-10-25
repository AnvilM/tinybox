<?php

declare(strict_types=1);

namespace App\Commands\Shared\Options;

use App\Application\Outbound\Selection\Exception\SyntaxException;
use App\Application\Outbound\Selection\Node\AllOfNode;
use App\Application\Outbound\Selection\Node\AnyOfNode;
use App\Application\Outbound\Selection\Node\NotNode;
use App\Application\Outbound\Selection\Node\SelectorNode;
use App\Application\Outbound\Selection\Syntax\SelectorParser;
use Iva\Input\Input;
use Iva\Input\Option;
use LogicException;

/**
 * The outbound selection option block: `--select` and `--exclude`.
 *
 * A command that needs a second, prefixed copy of the block (Export's urltest
 * outbounds) calls configureSelectionOptions() with includeUrltestVariant:
 * true, then resolves either variant independently:
 *
 *   $main    = $this->resolveSelector($input, $this->selectorParser);
 *   $urltest = $this->resolveSelector($input, $this->selectorParser, self::URLTEST_FILTER_PREFIX);
 */
trait SelectionOptionsTrait
{
    /** @var array<string, SelectionOptionHandles> */
    private array $selectionOptionHandles = [];

    protected function configureSelectionOptions(
        bool   $includeUrltestVariant = false,
        string $urltestPrefix = 'urltest',
    ): void
    {
        $this->selectionOptionHandles[''] = $this->buildSelectionOptionsBlock('');

        if ($includeUrltestVariant) {
            $this->selectionOptionHandles[$urltestPrefix] = $this->buildSelectionOptionsBlock($urltestPrefix);
        }
    }

    private function buildSelectionOptionsBlock(string $prefix): SelectionOptionHandles
    {
        $name = fn(string $option): string => $this->selectionOptionName($prefix, $option);

        return new SelectionOptionHandles(
            select: $this->addOption(Option::strings(
                name: $name('select'),
                description: 'Keep only outbounds matching the expression, repeatable (combined with AND). '
                    . 'Syntax: property (=|!=|~|!~) value[,value], combined with & | ! and parentheses; '
                    . 'properties: type, tag; ~ is a glob (* and ?). '
                    . "Example: --{$name('select')} 'type=vless,ss & tag!~RU-*' (quote it in the shell)",
            )),
            exclude: $this->addOption(Option::strings(
                name: $name('exclude'),
                shortcut: $prefix === '' ? 'e' : null,
                description: 'Remove outbounds matching the expression, repeatable (combined with OR). '
                    . "Same syntax as --{$name('select')}. "
                    . "Example: --{$name('exclude')} 'type=ss & tag!=my-vps'",
            )),
        );
    }

    private function selectionOptionName(string $prefix, string $name): string
    {
        return $prefix === '' ? $name : $prefix . ucfirst($name);
    }

    /**
     * Builds the selection tree of one option block:
     *
     *   AllOf( select1, ..., selectN, Not( AnyOf( exclude1, ..., excludeM ) ) )
     *
     * @return SelectorNode|null null - no flags given, everything is selected
     *
     * @throws SyntaxException
     */
    protected function resolveSelector(Input $input, SelectorParser $parser, string $prefix = ''): ?SelectorNode
    {
        $handles = $this->selectionOptionHandles[$prefix] ?? throw new LogicException(sprintf(
            'Selection options for prefix "%s" were never configured. Call configureSelectionOptions() from configureCommand() first.',
            $prefix,
        ));

        $nodes = array_map(
            static fn(string $expression): SelectorNode => $parser->parse($expression),
            array_values($input->option($handles->select)),
        );

        $excludes = array_map(
            static fn(string $expression): SelectorNode => $parser->parse($expression),
            array_values($input->option($handles->exclude)),
        );

        if ($excludes !== []) {
            $nodes[] = new NotNode(count($excludes) === 1 ? $excludes[0] : new AnyOfNode($excludes));
        }

        return match (count($nodes)) {
            0 => null,
            1 => $nodes[0],
            default => new AllOfNode($nodes),
        };
    }
}
