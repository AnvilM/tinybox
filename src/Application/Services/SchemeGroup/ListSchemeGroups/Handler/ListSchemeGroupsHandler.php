<?php

declare(strict_types=1);

namespace App\Application\Services\SchemeGroup\ListSchemeGroups\Handler;

use App\Application\Exception\Repository\Shared\UnableToGetListException;
use App\Application\Repository\SchemeGroup\GetSchemeGroupListRepository;
use App\Domain\Shared\Exception\CriticalException;
use Psl\Collection\MutableVector;

final readonly class ListSchemeGroupsHandler
{
    public function __construct(
        private GetSchemeGroupListRepository $getSchemeGroupsList,
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
            return $this->getSchemeGroupsList->getSchemeGroupsList()->getSchemeGroupNames();
        } catch (UnableToGetListException $e) {
            throw new CriticalException($e->getMessage(), $e->getDebugMessage());
        }
    }
}