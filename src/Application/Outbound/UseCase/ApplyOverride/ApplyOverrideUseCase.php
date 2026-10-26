<?php

declare(strict_types=1);

namespace App\Application\Outbound\UseCase\ApplyOverride;

use App\Application\Outbound\DTO\UseCase\ApplyOverride\ApplyOverrideDTO;
use App\Application\Outbound\DTO\UseCase\SelectOutbounds\SelectOutboundsDTO;
use App\Application\Outbound\Exception\Override\EmptyOverrideRuleException;
use App\Application\Outbound\Override\OverrideRegistry;
use App\Application\Outbound\Selection\Exception\SelectionException;
use App\Application\Outbound\UseCase\SelectOutbounds\SelectOutboundsUseCase;
use App\Domain\Outbound\Collection\OutboundMap;

final readonly class ApplyOverrideUseCase
{
    public function __construct(
        private SelectOutboundsUseCase $selectOutboundsUseCase,
        private OverrideRegistry       $registry,
    )
    {
    }

    /**
     * Applies one override rule to the outbounds selected from the CURRENT set.
     *
     * @return OutboundMap New set, same order, selected outbounds replaced by their overridden copies
     *
     * @throws SelectionException
     * @throws EmptyOverrideRuleException If the expression matched no outbounds
     */
    public function handle(ApplyOverrideDTO $dto): OutboundMap
    {
        $definition = $dto->definition;

        $matched = $this->selectOutboundsUseCase->handle(
            new SelectOutboundsDTO($dto->outbounds, selector: $definition->selector)
        );

        if ($matched->isEmpty()) {
            throw new EmptyOverrideRuleException($definition->source);
        }

        $overridden = $this->registry->byType($definition->type::class)
            ->override($matched, $definition->type);

        return $this->replaceById($dto->outbounds, $overridden);
    }

    /**
     * Overridden copies keep the id of the original, so the set is rebuilt in the same order
     */
    private function replaceById(OutboundMap $base, OutboundMap $replacements): OutboundMap
    {
        $result = $base->createEmpty();

        foreach ($base->getOutbounds() as $outbound) {
            $result->add($replacements->getMap()->get($outbound->getIdString()) ?? $outbound);
        }

        return $result;
    }
}
