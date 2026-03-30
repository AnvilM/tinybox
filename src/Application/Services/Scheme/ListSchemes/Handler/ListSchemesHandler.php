<?php

declare(strict_types=1);

namespace App\Application\Services\Scheme\ListSchemes\Handler;

use App\Application\Exception\Repository\Scheme\UnableToGetSchemesListException;
use App\Application\Repository\Scheme\GetSchemesList;
use App\Domain\Shared\Exception\CriticalException;
use Psl\Collection\MutableMap;

final readonly class ListSchemesHandler
{
    public function __construct(
        private GetSchemesList $getSchemesList,
    )
    {
    }

    /**
     * @throws CriticalException
     */
    public function handle(): MutableMap
    {
        try {
            return $this->getSchemesList->getSchemesList()->getMap();
        } catch (UnableToGetSchemesListException $e) {
            throw new CriticalException("Unable to get schemes list", $e->getMessage());
        }
    }
}