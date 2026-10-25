<?php

declare(strict_types=1);

namespace App\Application\Urltest\UseCase\BuildUrltestGroups;

use App\Application\Outbound\DTO\UseCase\SelectOutbounds\SelectOutboundsDTO;
use App\Application\Outbound\Selection\Exception\SelectionException;
use App\Application\Outbound\UseCase\SelectOutbounds\SelectOutboundsUseCase;
use App\Application\Urltest\DTO\UseCase\BuildUrltestGroups\BuildUrltestGroupsDTO;
use App\Application\Urltest\Exception\EmptyUrltestGroupException;
use App\Application\Urltest\Group\UrltestGroup;
use Psl\Collection\MutableVector;
use Psl\Collection\VectorInterface;

final readonly class BuildUrltestGroupsUseCase
{
    public function __construct(
        private SelectOutboundsUseCase $selectOutboundsUseCase,
    )
    {
    }

    /**
     * @return VectorInterface<UrltestGroup> Groups in the order of definitions
     *
     * @throws SelectionException
     * @throws EmptyUrltestGroupException If no outbound got into a group
     */
    public function handle(BuildUrltestGroupsDTO $dto): VectorInterface
    {
        /** @var MutableVector<UrltestGroup> $groups */
        $groups = new MutableVector([]);

        foreach ($dto->definitions as $definition) {
            $outbounds = $definition->selector === null
                ? clone $dto->outboundsMap
                : $this->selectOutboundsUseCase->handle(
                    new SelectOutboundsDTO($dto->outboundsMap, selector: $definition->selector)
                );

            if ($outbounds->isEmpty()) {
                throw new EmptyUrltestGroupException($definition->source);
            }

            $groups->add(new UrltestGroup($definition->tag, $outbounds));
        }

        return $groups;
    }
}
