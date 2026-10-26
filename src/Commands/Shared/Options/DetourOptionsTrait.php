<?php

declare(strict_types=1);

namespace App\Commands\Shared\Options;

use App\Application\Outbound\Detour\DetourDefinition;
use App\Application\Outbound\Detour\DetourSpecParser;
use App\Application\Outbound\Exception\Detour\InvalidDetourSpecException;
use App\Application\Outbound\Selection\Exception\SelectionException;
use App\Commands\Shared\Options\Pipeline\PipelineKind;
use Iva\Input\Input;
use Iva\Input\Option;
use Psl\Collection\Vector;
use Psl\Collection\VectorInterface;

/**
 * Detour option block: repeatable `--detour DETOUR_TAG:EXPRESSION` (`-d`).
 *
 * Only handles input: every SPEC string is parsed by DetourSpecParser.
 */
trait DetourOptionsTrait
{
    private Option $detourOption;

    protected function configureDetourOptions(): void
    {
        $this->detourOption = $this->addOption(Option::strings(
            name: PipelineKind::Detour->long(),
            shortcut: PipelineKind::Detour->short(),
            description: 'Set a detour to the outbounds selected by the expression, repeatable (one rule per flag). '
                . 'Format: DETOUR_TAG:EXPRESSION, the colon is required; DETOUR_TAG is the tag of an existing outbound '
                . "(put it in quotes if it contains ':', e.g. -d \"'RU: 1':type=vless\"); "
                . 'empty EXPRESSION - all outbounds. '
                . 'Expression syntax is the same as in --select. '
                . 'The first matching rule wins: an outbound that already has a detour is skipped by the next rules. '
                . 'In export the rules are applied in the order of the flags together with --override and --urltest: '
                . 'the target must be created by an earlier flag. '
                . "Example: -d 'proxy-1:type=vless' -d 'proxy-2:' (quote it in the shell)",
        ));
    }

    /**
     * @return VectorInterface<DetourDefinition> Definitions in the order of flags, empty if no flags given
     *
     * @throws InvalidDetourSpecException
     * @throws SelectionException
     */
    protected function resolveDetourDefinitions(Input $input, DetourSpecParser $parser): VectorInterface
    {
        return new Vector(array_map(
            static fn(string $spec): DetourDefinition => $parser->parse($spec),
            array_values($input->option($this->detourOption)),
        ));
    }
}
