<?php

declare(strict_types=1);

namespace App\Application\Outbound\UseCase\RunOutboundPipeline;

use App\Application\Outbound\Detour\DetourDefinition;
use App\Application\Outbound\DTO\UseCase\ApplyDetours\ApplyDetoursDTO;
use App\Application\Outbound\DTO\UseCase\ApplyOverride\ApplyOverrideDTO;
use App\Application\Outbound\DTO\UseCase\BuildUrltestOutbound\BuildUrltestOutboundDTO;
use App\Application\Outbound\DTO\UseCase\RunOutboundPipeline\RunOutboundPipelineDTO;
use App\Application\Outbound\Exception\Detour\DetourTargetNotFoundException;
use App\Application\Outbound\Exception\Detour\EmptyDetourRuleException;
use App\Application\Outbound\Exception\Detour\UnsupportedDetourTargetException;
use App\Application\Outbound\Exception\Override\EmptyOverrideRuleException;
use App\Application\Outbound\Exception\Urltest\DuplicateUrltestTagException;
use App\Application\Outbound\Exception\Urltest\EmptyUrltestGroupException;
use App\Application\Outbound\Override\OverrideDefinition;
use App\Application\Outbound\Selection\Exception\SelectionException;
use App\Application\Outbound\Urltest\UrltestGroupDefinition;
use App\Application\Outbound\Urltest\UrltestPreparer;
use App\Application\Outbound\UseCase\ApplyDetours\ApplyDetoursUseCase;
use App\Application\Outbound\UseCase\ApplyOverride\ApplyOverrideUseCase;
use App\Application\Outbound\UseCase\BuildUrltestOutbound\BuildUrltestOutboundUseCase;
use App\Domain\Outbound\Collection\OutboundMap;
use LogicException;
use Psl\Collection\Vector;

/**
 * Executes the steps (override rules, urltest groups, detour rules) strictly in the order of the flags.
 *
 * The pipeline owns one set of outbounds. Every step selects from the set as it is
 * at that moment, so it sees the result of all the previous steps.
 */
final readonly class RunOutboundPipelineUseCase
{
    public function __construct(
        private ApplyOverrideUseCase        $applyOverrideUseCase,
        private BuildUrltestOutboundUseCase $buildUrltestOutboundUseCase,
        private ApplyDetoursUseCase         $applyDetoursUseCase,
        private UrltestPreparer             $urltestPreparer,
    )
    {
    }

    /**
     * @return OutboundMap Final set: regular outbounds (overridden, with detours) + urltests
     *
     * @throws SelectionException
     * @throws EmptyOverrideRuleException
     * @throws EmptyUrltestGroupException
     * @throws DuplicateUrltestTagException
     * @throws DetourTargetNotFoundException
     * @throws EmptyDetourRuleException
     * @throws UnsupportedDetourTargetException
     */
    public function handle(RunOutboundPipelineDTO $dto): OutboundMap
    {
        /**
         * Preparation: no selection, no side effects.
         * Tags of all urltest groups are checked before any step is executed.
         */
        $urltestDefinitions = [];

        foreach ($dto->steps as $step) {
            if ($step instanceof UrltestGroupDefinition) {
                $urltestDefinitions[] = $step;
            }
        }

        $preparedGroups = $this->urltestPreparer->prepare($urltestDefinitions, $dto->core, $dto->outbounds);
        $urltestIndex = 0;


        /**
         * The pipeline owns its container
         */
        $set = clone $dto->outbounds;

        foreach ($dto->steps as $step) {
            if ($step instanceof OverrideDefinition) {
                /**
                 * Override returns a new set: the next step gets what this one returned
                 */
                $set = $this->applyOverrideUseCase->handle(new ApplyOverrideDTO($set, $step));

                continue;
            }

            if ($step instanceof UrltestGroupDefinition) {
                $urltest = $this->buildUrltestOutboundUseCase->handle(
                    new BuildUrltestOutboundDTO($set, $preparedGroups[$urltestIndex++], $dto->core)
                );

                $set->add($urltest);

                continue;
            }

            if ($step instanceof DetourDefinition) {
                /**
                 * Detour is set on the objects in place, the set stays the same
                 */
                $this->applyDetoursUseCase->handle(
                    new ApplyDetoursDTO($set, new Vector([$step]), $dto->core)
                );

                continue;
            }

            throw new LogicException(sprintf("Unsupported pipeline step '%s'", $step::class));
        }

        return $set;
    }
}
