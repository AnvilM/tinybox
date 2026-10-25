<?php

declare(strict_types=1);

namespace App\Commands\Shared\Options;

use App\Application\Outbound\Selection\Exception\SelectionException;
use App\Application\Outbound\Selection\Node\SelectorNode;
use App\Application\Outbound\Selection\SelectorComposer;
use App\Application\Outbound\Selection\Syntax\SelectorParser;
use Iva\Input\Input;
use Iva\Input\Option;

/**
 * Global selection option block: `--select` and `--exclude`.
 *
 * Selects the outbounds that go into the final config. Only handles input:
 * parsing is done by SelectorParser, tree composition by SelectorComposer.
 */
trait GlobalSelectionOptionsTrait
{
    private Option $selectOption;
    private Option $excludeOption;

    protected function configureGlobalSelectionOptions(): void
    {
        $this->selectOption = $this->addOption(Option::strings(
            name: 'select',
            description: 'Keep only outbounds matching the expression, repeatable (combined with AND). '
                . 'Syntax: property (=|!=|~|!~) value[,value], combined with & | ! and parentheses; '
                . 'properties: type, tag, server, port, security, sni, fingerprint, transport, flow, method, plugin; ~ is a glob (* and ?, strings only). '
                . "Example: --select 'type=vless,ss & tag!~RU-*' (quote it in the shell)",
        ));

        $this->excludeOption = $this->addOption(Option::strings(
            name: 'exclude',
            shortcut: 'e',
            description: 'Remove outbounds matching the expression, repeatable (combined with OR). '
                . 'Same syntax as --select. '
                . "Example: --exclude 'type=ss & tag!=my-vps'",
        ));
    }

    /**
     * @return SelectorNode|null null - no flags given, everything is selected
     *
     * @throws SelectionException
     */
    protected function resolveGlobalSelector(Input $input, SelectorParser $parser, SelectorComposer $composer): ?SelectorNode
    {
        $parse = static fn(string $expression): SelectorNode => $parser->parse($expression);

        return $composer->compose(
            array_map($parse, array_values($input->option($this->selectOption))),
            array_map($parse, array_values($input->option($this->excludeOption))),
        );
    }
}
