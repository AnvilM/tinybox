<?php

declare(strict_types=1);

namespace App\Application\Outbound\UseCase\ApplyDetours;

use App\Application\Outbound\DTO\Export\CoreType;
use App\Application\Outbound\DTO\UseCase\ApplyDetours\ApplyDetoursDTO;
use App\Application\Outbound\DTO\UseCase\SelectOutbounds\SelectOutboundsDTO;
use App\Application\Outbound\Exception\Detour\DetourTargetNotFoundException;
use App\Application\Outbound\Exception\Detour\EmptyDetourRuleException;
use App\Application\Outbound\Exception\Detour\UnsupportedDetourTargetException;
use App\Application\Outbound\Selection\Exception\SelectionException;
use App\Application\Outbound\UseCase\SelectOutbounds\SelectOutboundsUseCase;
use App\Domain\Interface\Outbound\DetourProvider;
use App\Domain\Outbound\Collection\UniqueTagOutboundsMap;
use App\Domain\Outbound\Entity\UrltestOutbound;
use App\Domain\Outbound\Exception\OutboundNotFoundException;

final readonly class ApplyDetoursUseCase
{
    public function __construct(
        private SelectOutboundsUseCase $selectOutboundsUseCase,
    )
    {
    }

    /**
     * Set detours to the outbounds of the set, the map is not rebuilt.
     *
     * NOTE: The first rule wins: an outbound that already has a detour is skipped by the next rules.
     *
     * @throws SelectionException
     * @throws DetourTargetNotFoundException
     * @throws UnsupportedDetourTargetException
     * @throws EmptyDetourRuleException
     */
    public function handle(ApplyDetoursDTO $dto): void
    {
        foreach ($dto->definitions as $definition) {
            /**
             * Find the target by tag
             */
            try {
                $target = (new UniqueTagOutboundsMap($dto->outbounds->getOutbounds()))->getWithTag($definition->targetTag);
            } catch (OutboundNotFoundException) {
                throw new DetourTargetNotFoundException($definition->targetTag, $definition->source);
            }

            if ($dto->core === CoreType::Xray && $target instanceof UrltestOutbound) {
                throw new UnsupportedDetourTargetException($target->getTagString());
            }


            /**
             * Select the subjects
             */
            $subjects = $definition->selector === null
                ? clone $dto->outbounds
                : $this->selectOutboundsUseCase->handle(
                    new SelectOutboundsDTO($dto->outbounds, selector: $definition->selector)
                );

            if ($subjects->isEmpty()) {
                throw new EmptyDetourRuleException($definition->source);
            }

            foreach ($subjects->getOutbounds() as $subject) {
                if (!($subject instanceof DetourProvider)) continue;

                if ($subject->getTagString() === $target->getTagString()) continue;

                if ($subject->getDetour() !== null) continue;

                $subject->setDetour($target);
            }
        }
    }
}
