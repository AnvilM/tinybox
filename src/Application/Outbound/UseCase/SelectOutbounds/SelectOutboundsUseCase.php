<?php

declare(strict_types=1);

namespace App\Application\Outbound\UseCase\SelectOutbounds;

use App\Application\Outbound\DTO\UseCase\SelectOutbounds\SelectOutboundsDTO;
use App\Application\Outbound\Selection\Exception\SelectionException;
use App\Application\Outbound\Selection\SelectorCompiler;
use App\Domain\Interface\Outbound\OutboundSpecificationInterface;
use App\Domain\Outbound\Collection\OutboundMap;
use Psl\Collection\MutableVector;

final readonly class SelectOutboundsUseCase
{
    public function __construct(
        private SelectorCompiler $compiler,
    )
    {
    }

    /**
     * @throws SelectionException
     */
    public function handle(SelectOutboundsDTO $dto): OutboundMap
    {
        /** @var MutableVector<OutboundSpecificationInterface> $specifications */
        $specifications = new MutableVector([]);

        if ($dto->selector !== null) {
            $specifications->add($this->compiler->compile($dto->selector));
        }

        foreach ($dto->constraints as $constraint) {
            $specifications->add($constraint);
        }

        return $dto->outboundsMap->filter($specifications);
    }
}
