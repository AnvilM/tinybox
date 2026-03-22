<?php

declare(strict_types=1);

namespace App\Application\Services\SchemeGroup\AddSchemeToSchemeGroup\Handler;

use App\Application\Services\SchemeGroup\AddSchemeToSchemeGroup\Command\AddSchemeToSchemeGroupCommand;
use App\Application\Shared\SchemeGroup\Shared\File\WriteSchemeGroups;
use App\Application\Shared\SchemeGroup\UseCase\ReadSchemeGroupsList\ReadSchemeGroupsListUseCase;
use App\Application\Shared\Shared\Shared\Scheme\UseCase\ReadSchemesList\ReadSchemesListUseCase;
use App\Domain\Scheme\Collection\UniqueSchemesMap;
use App\Domain\Scheme\Exception\SchemeAlreadyExistsException;
use App\Domain\Scheme\Exception\SchemeNotFoundException;
use App\Domain\SchemeGroup\Entity\SchemeGroup;
use App\Domain\SchemeGroup\Exception\SchemeGroupAlreadyExistsException;
use App\Domain\SchemeGroup\Exception\SchemeGroupNotFoundException;
use App\Domain\Shared\Exception\CriticalException;
use App\Domain\Shared\VO\Shared\NonEmptyStringVO;
use InvalidArgumentException;

final readonly class AddSchemeToSchemeGroupHandler
{
    public function __construct(
        private ReadSchemesListUseCase      $readSchemesListUseCase,
        private ReadSchemeGroupsListUseCase $readSchemeGroupsListUseCase,
        private WriteSchemeGroups           $writeSchemeGroups,
    )
    {
    }

    /**
     * Add scheme to schemeGroup
     *
     * @param AddSchemeToSchemeGroupCommand $command Command with schemeGroup name and scheme id
     *
     * @throws CriticalException
     */
    public function handle(AddSchemeToSchemeGroupCommand $command): void
    {
        /**
         * Read schemes list
         */
        $schemes = $this->readSchemesListUseCase->handle();


        /**
         * Try to find scheme with provided id in schemes list
         */
        try {
            $scheme = $schemes->getById($command->schemeId);
        } catch (SchemeNotFoundException) {
            throw new CriticalException("Scheme with id $command->schemeId does not exist");
        }


        /**
         * Read schemeGroups list
         */
        $schemeGroups = $this->readSchemeGroupsListUseCase->handle();


        /**
         * Try to create schemeGroup name
         */
        try {
            $schemeGroupName = new NonEmptyStringVO($command->name);
        } catch (InvalidArgumentException) {
            throw new CriticalException("Invalid scheme group name provided", $command->name);
        }


        /**
         * Try to find scheme group with provided name in schemeGroups list
         */
        try {
            $schemeGroups->getByName($schemeGroupName)->getSchemes()->add($scheme);
        } catch (SchemeGroupNotFoundException) {
            $schemes = new UniqueSchemesMap();

            /**
             * Try to create new schemeGroup with scheme found by provided scheme id
             */
            try {
                $schemes->add($scheme);

                $schemeGroups->add(new SchemeGroup(
                    $schemeGroupName,
                    $schemes
                ));
            } catch (SchemeAlreadyExistsException) {
                throw new CriticalException("Scheme with id {$scheme->getHash()} already exists in schemeGroup {$schemeGroupName->getValue()} ", $command->schemeId);
            } catch (SchemeGroupAlreadyExistsException) {
                throw new CriticalException("Unknown error");
            }
        } catch (SchemeAlreadyExistsException) {
            throw new CriticalException("Scheme with id {$scheme->getHash()} already exists in schemeGroup {$schemeGroupName->getValue()} ", $command->schemeId);

        }


        /**
         * Write schemeGroups list to file
         */
        $this->writeSchemeGroups->write(
            $schemeGroups
        );

    }
}