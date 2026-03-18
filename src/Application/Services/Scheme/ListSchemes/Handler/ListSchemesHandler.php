<?php

declare(strict_types=1);

namespace App\Application\Services\Scheme\ListSchemes\Handler;

use App\Application\Shared\Shared\Shared\Scheme\UseCase\ReadSchemesList\ReadSchemesListUseCase;
use App\Domain\Shared\Exception\CriticalException;
use Psl\Collection\MutableMap;

final readonly class ListSchemesHandler
{
    public function __construct(
        private ReadSchemesListUseCase $readSchemesListUseCase,
    )
    {
    }

    /**
     * @throws CriticalException
     */
    public function handle(): MutableMap
    {
        return $this->readSchemesListUseCase->handle()->getMap();
    }
}