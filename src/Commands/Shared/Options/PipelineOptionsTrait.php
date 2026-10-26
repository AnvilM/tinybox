<?php

declare(strict_types=1);

namespace App\Commands\Shared\Options;

use App\Application\Outbound\Detour\DetourSpecParser;
use App\Application\Outbound\Exception\Detour\InvalidDetourSpecException;
use App\Application\Outbound\Exception\Override\InvalidOverrideSpecException;
use App\Application\Outbound\Exception\Override\InvalidOverrideValueException;
use App\Application\Outbound\Exception\Override\UnknownOverrideException;
use App\Application\Outbound\Exception\Urltest\InvalidUrltestGroupException;
use App\Application\Outbound\Override\OverrideSpecParser;
use App\Application\Outbound\Pipeline\PipelineStep;
use App\Application\Outbound\Selection\Exception\SelectionException;
use App\Application\Outbound\Urltest\UrltestGroupSpecParser;
use App\Commands\Shared\Options\Pipeline\FlagOrderMismatchException;
use App\Commands\Shared\Options\Pipeline\PipelineFlagOrder;
use App\Commands\Shared\Options\Pipeline\PipelineKind;
use Iva\Input\Input;
use Psl\Collection\Vector;
use Psl\Collection\VectorInterface;

/**
 * Pipeline option block: the flags whose rules are applied to the outbounds in the order
 * of the command line (--override, --urltest, --detour).
 *
 * Only handles input: parsing is done by the spec parsers, the order is read by PipelineFlagOrder.
 */
trait PipelineOptionsTrait
{
    use OverrideOptionsTrait;
    use UrltestOptionsTrait;
    use DetourOptionsTrait;

    protected function configurePipelineOptions(): void
    {
        $this->configureOverrideOptions();
        $this->configureUrltestOptions();
        $this->configureDetourOptions();
    }

    /**
     * @param bool $overridesOnly true - only --override steps are parsed (export as schemes),
     *                            so --urltest and --detour are not parsed
     *
     * @return VectorInterface<PipelineStep> Steps in the order of flags
     *
     * @throws InvalidOverrideSpecException
     * @throws UnknownOverrideException
     * @throws InvalidOverrideValueException
     * @throws InvalidUrltestGroupException
     * @throws InvalidDetourSpecException
     * @throws SelectionException
     * @throws FlagOrderMismatchException If the order of the flags does not match the parsed definitions
     */
    protected function resolvePipelineSteps(
        Input                  $input,
        PipelineFlagOrder      $flagOrder,
        OverrideSpecParser     $overrideParser,
        UrltestGroupSpecParser $urltestParser,
        DetourSpecParser       $detourParser,
        bool                   $overridesOnly = false,
    ): VectorInterface
    {
        /**
         * Definitions of every kind, in the order of flags of that kind
         *
         * @var array<string, list<PipelineStep>> $definitions
         */
        $definitions = [
            PipelineKind::Override->value => array_values($this->resolveOverrideDefinitions($input, $overrideParser)->toArray()),
        ];

        if (!$overridesOnly) {
            $definitions[PipelineKind::Urltest->value] = array_values($this->resolveUrltestDefinitions($input, $urltestParser)->toArray());
            $definitions[PipelineKind::Detour->value] = array_values($this->resolveDetourDefinitions($input, $detourParser)->toArray());
        }

        if (array_sum(array_map('count', $definitions)) === 0) {
            return new Vector([]);
        }

        /**
         * Take the next definition of the kind for every occurrence of the flag
         */
        $steps = [];
        $taken = [];

        foreach ($flagOrder->read($input) as $kind) {
            if (!isset($definitions[$kind->value])) continue;

            $index = $taken[$kind->value] ?? 0;

            if (!isset($definitions[$kind->value][$index])) {
                throw new FlagOrderMismatchException();
            }

            $steps[] = $definitions[$kind->value][$index];
            $taken[$kind->value] = $index + 1;
        }

        /**
         * The order must never be silently wrong: every definition has to get its place
         */
        foreach ($definitions as $kind => $kindDefinitions) {
            if (($taken[$kind] ?? 0) !== count($kindDefinitions)) {
                throw new FlagOrderMismatchException();
            }
        }

        return new Vector($steps);
    }
}
