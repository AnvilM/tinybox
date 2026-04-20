<?php

declare(strict_types=1);

namespace App\Application\Group\UseCase\GetGroupsList;

use App\Application\Exception\Repository\Shared\UnableToGetListException;
use App\Application\Repository\Group\GetGroupListRepository;
use App\Domain\Shared\Exception\CriticalException;
use Psl\Collection\MutableVector;

final readonly class GetGroupsListUseCase
{
    public function __construct(
        private GetGroupListRepository $getGroupsList,
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
        try {
            return $this->getGroupsList->getGroupsList()->getGroupNames();
        } catch (UnableToGetListException $e) {
            throw new CriticalException($e->getMessage(), $e->getDebugMessage());
        }
    }
}