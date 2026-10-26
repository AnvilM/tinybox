<?php

declare(strict_types=1);

namespace App\Commands\Shared\Options;

use App\Application\Outbound\Selection\Exception\SelectionException;
use App\Application\Outbound\Exception\Urltest\InvalidUrltestGroupException;
use App\Application\Outbound\Urltest\UrltestGroupDefinition;
use App\Application\Outbound\Urltest\UrltestGroupSpecParser;
use App\Commands\Shared\Options\Pipeline\PipelineKind;
use Iva\Input\Input;
use Iva\Input\Option;
use Psl\Collection\Vector;
use Psl\Collection\VectorInterface;

/**
 * Urltest option block: repeatable `--urltest TAG:EXPRESSION` (`-u`).
 *
 * Only handles input: every SPEC string is parsed by UrltestGroupSpecParser.
 */
trait UrltestOptionsTrait
{
    private Option $urltestOption;

    protected function configureUrltestOptions(): void
    {
        $this->urltestOption = $this->addOption(Option::strings(
            name: PipelineKind::Urltest->long(),
            shortcut: PipelineKind::Urltest->short(),
            description: 'Add a urltest group to the config, repeatable (one group per flag). '
                . 'Format: TAG:EXPRESSION, the colon is required; empty TAG - tag from the template, '
                . 'empty EXPRESSION - all selected outbounds. '
                . 'Expression syntax is the same as in --select. '
                . 'Groups are applied in the order of the flags together with --override and --detour: '
                . 'a group sees the result of the earlier flags. '
                . "Example: -u 'auto-vless:type=vless & tag!~RU-*' -u 'auto-ss:type=ss' (quote it in the shell)",
        ));
    }

    /**
     * @return VectorInterface<UrltestGroupDefinition> Definitions in the order of flags, empty if no flags given
     *
     * @throws InvalidUrltestGroupException
     * @throws SelectionException
     */
    protected function resolveUrltestDefinitions(Input $input, UrltestGroupSpecParser $parser): VectorInterface
    {
        return new Vector(array_map(
            static fn(string $spec): UrltestGroupDefinition => $parser->parse($spec),
            array_values($input->option($this->urltestOption)),
        ));
    }
}
