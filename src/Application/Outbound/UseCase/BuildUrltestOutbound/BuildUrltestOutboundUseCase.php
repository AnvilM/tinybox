<?php

declare(strict_types=1);

namespace App\Application\Outbound\UseCase\BuildUrltestOutbound;

use App\Application\Outbound\DTO\Export\CoreType;
use App\Application\Outbound\DTO\UseCase\BuildUrltestOutbound\BuildUrltestOutboundDTO;
use App\Application\Outbound\DTO\UseCase\SelectOutbounds\SelectOutboundsDTO;
use App\Application\Outbound\Exception\Urltest\EmptyUrltestGroupException;
use App\Application\Outbound\Selection\Exception\SelectionException;
use App\Application\Outbound\Urltest\Specification\NoUrltestSpecification;
use App\Application\Outbound\UseCase\SelectOutbounds\SelectOutboundsUseCase;
use App\Domain\Outbound\Entity\UrltestOutbound;
use App\Domain\Outbound\Factory\UrltestOutboundFactory;
use Psl\Collection\Vector;

final readonly class BuildUrltestOutboundUseCase
{
    public function __construct(
        private SelectOutboundsUseCase $selectOutboundsUseCase,
        private UrltestOutboundFactory $urltestOutboundFactory,
    )
    {
    }

    /**
     * Create one urltest outbound from the CURRENT set of the pipeline.
     *
     * NOTE: For sing-box the group can select the urltests created earlier (nesting).
     * For xray the members are selected only among non-urltest outbounds:
     * balancer can not be a member of another balancer.
     *
     * @throws SelectionException
     * @throws EmptyUrltestGroupException If no outbound got into the group
     */
    public function handle(BuildUrltestOutboundDTO $dto): UrltestOutbound
    {
        $constraints = $dto->core === CoreType::Xray
            ? new Vector([new NoUrltestSpecification()])
            : new Vector([]);

        $members = $this->selectOutboundsUseCase->handle(new SelectOutboundsDTO(
            $dto->pool,
            selector: $dto->group->definition->selector,
            constraints: $constraints,
        ));

        if ($members->isEmpty()) {
            throw new EmptyUrltestGroupException($dto->group->definition->source);
        }

        return $this->urltestOutboundFactory->create($dto->group->tag, $members, $dto->group->parameters);
    }
}
