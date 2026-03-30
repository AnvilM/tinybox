<?php

declare(strict_types=1);

namespace App\Application\Services\SchemeGroup\AddSchemeToSchemeGroup\Handler;

use App\Application\Exception\Repository\Scheme\UnableToGetSchemesListException;
use App\Application\Exception\Repository\Shared\UnableToGetListException;
use App\Application\Exception\Repository\Shared\UnableToSaveListException;
use App\Application\Repository\Scheme\GetSchemesListRepository;
use App\Application\Repository\SchemeGroup\AddSchemeToSchemeGroupOrCreateNewRepository;
use App\Application\Repository\SchemeGroup\SaveSchemeGroupListRepository;
use App\Application\Services\SchemeGroup\AddSchemeToSchemeGroup\Command\AddSchemeToSchemeGroupCommand;
use App\Domain\Scheme\Exception\SchemeAlreadyExistsException;
use App\Domain\Scheme\Exception\SchemeNotFoundException;
use App\Domain\Shared\Exception\CriticalException;
use App\Domain\Shared\VO\Shared\NonEmptyStringVO;
use InvalidArgumentException;

final readonly class AddSchemeToSchemeGroupHandler
{
    public function __construct(
        private GetSchemesListRepository                    $getSchemesList,
        private AddSchemeToSchemeGroupOrCreateNewRepository $addSchemeToSchemeGroupOrCreateNewRepository,
        private SaveSchemeGroupListRepository               $saveSchemeGroupListRepository,
    )
    {
    }

    /**
     * Add scheme to schemeGroup or create new scheme group with provided scheme
     *
     * @param AddSchemeToSchemeGroupCommand $command Command with schemeGroup name and scheme id
     *
     * @throws CriticalException
     */
    public function handle(AddSchemeToSchemeGroupCommand $command): void
    {

        /**
         * Try to read schemes list
         */
        try {
            $schemes = $this->getSchemesList->get();
        } catch (UnableToGetSchemesListException $e) {
            throw new CriticalException($e->getMessage(), $e->getDebugMessage());
        }


        /**
         * Try to get scheme with provided id
         */
        try {
            $scheme = $schemes->getById($command->schemeId);
        } catch (SchemeNotFoundException $e) {
            throw new CriticalException("Scheme with id $command->schemeId does not exist", $e->getDebugMessage());
        }


        /**
         * Try to create schemeGroup name
         */
        try {
            $schemeGroupName = new NonEmptyStringVO($command->name);
        } catch (InvalidArgumentException) {
            throw new CriticalException("Invalid scheme group name provided", $command->name);
        }


        /**
         * Try to add scheme to scheme group or create new
         * scheme group list with provided scheme and save scheme groups list
         */
        try {
            $this->addSchemeToSchemeGroupOrCreateNewRepository->add($schemeGroupName, $scheme);
            $this->saveSchemeGroupListRepository->save();
        } catch (UnableToGetListException|SchemeAlreadyExistsException|UnableToSaveListException $e) {
            throw new CriticalException(match (true) {
                $e instanceof SchemeAlreadyExistsException => "Scheme with id $command->schemeId already exists in scheme group with name $command->name",
                $e instanceof UnableToGetListException => "Unable to get subscriptions list",
                $e instanceof UnableToSaveListException => "Unable to add scheme"
            }, $e->getDebugMessage());
        }
    }
}