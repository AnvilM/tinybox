<?php

declare(strict_types=1);

namespace App\Commands\Shared\Options;

use App\Application\Outbound\Exception\Override\InvalidOverrideSpecException;
use App\Application\Outbound\Exception\Override\InvalidOverrideValueException;
use App\Application\Outbound\Exception\Override\UnknownOverrideException;
use App\Application\Outbound\Override\OverrideDefinition;
use App\Application\Outbound\Override\OverrideSpecParser;
use App\Application\Outbound\Selection\Exception\SelectionException;
use App\Commands\Shared\Options\Pipeline\PipelineKind;
use Iva\Input\Input;
use Iva\Input\Option;
use Psl\Collection\Vector;
use Psl\Collection\VectorInterface;

/**
 * Override option block: repeatable `--override ACTION[=VALUE]:EXPRESSION`.
 *
 * Only handles input: every SPEC string is parsed by OverrideSpecParser.
 */
trait OverrideOptionsTrait
{
    private Option $overrideOption;

    protected function configureOverrideOptions(): void
    {
        $this->overrideOption = $this->addOption(Option::strings(
            name: PipelineKind::Override->long(),
            description: 'Override outbound fields for the outbounds selected by the expression, repeatable '
                . '(one rule per flag). Format: ACTION[=VALUE]:EXPRESSION, the colon is required; '
                . 'empty EXPRESSION - all outbounds of the current set. '
                . 'Actions: vless-uuid=UUID, ss-pass=PASSWORD, tls-sni=HOST, tls-insecure[=true|false], '
                . 'tls-verify-name-from-sni, resolve-dns. '
                . "Put the value in quotes if it contains ':', e.g. --override \"ss-pass='a:b':type=ss\". "
                . 'Expression syntax is the same as in --select. '
                . 'Rules are applied in the order of the flags together with --urltest and --detour: '
                . 'a rule sees the result of the earlier flags. '
                . "Example: --override 'tls-sni=cdn.example.com:type=vless & security=tls'",
        ));
    }

    /**
     * @return VectorInterface<OverrideDefinition> Definitions in the order of flags, empty if no flags given
     *
     * @throws InvalidOverrideSpecException
     * @throws UnknownOverrideException
     * @throws InvalidOverrideValueException
     * @throws SelectionException
     */
    protected function resolveOverrideDefinitions(Input $input, OverrideSpecParser $parser): VectorInterface
    {
        return new Vector(array_map(
            static fn(string $spec): OverrideDefinition => $parser->parse($spec),
            array_values($input->option($this->overrideOption)),
        ));
    }
}
