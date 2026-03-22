<?php

declare(strict_types=1);

namespace App\Application\Services\SchemeGroup\ListSchemeGroups\Handler;

use App\Application\Shared\SchemeGroup\UseCase\ReadSchemeGroupsList\ReadSchemeGroupsListUseCase;
use App\Domain\Shared\Exception\CriticalException;
use Psl\Collection\MutableVector;

final readonly class ListSchemeGroupsHandler
{
    public function __construct(
        private ReadSchemeGroupsListUseCase $readSchemeGroupsListUseCase,
    )
    {
    }

    /**
     * @return MutableVector<string> Vector of schemeGroup names
     *
     * @throws CriticalException
     */
    public function handle(): MutableVector
    {
        return $this->readSchemeGroupsListUseCase->handle()->getSchemeGroupNames();
    }
}