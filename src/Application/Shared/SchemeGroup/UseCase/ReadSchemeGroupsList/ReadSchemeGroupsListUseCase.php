<?php

declare(strict_types=1);

namespace App\Application\Shared\SchemeGroup\UseCase\ReadSchemeGroupsList;

use App\Application\Shared\SchemeGroup\Exception\Shared\Validator\InvalidSchemeGroupListFormatException;
use App\Application\Shared\SchemeGroup\Shared\File\ReadSchemeGroups;
use App\Application\Shared\SchemeGroup\Shared\Validator\SchemeGroupsListFormatValidator;
use App\Application\Shared\Shared\Shared\Scheme\UseCase\ReadSchemesList\ReadSchemesListUseCase;
use App\Domain\Scheme\Collection\UniqueSchemesMap;
use App\Domain\Scheme\Exception\SchemeAlreadyExistsException;
use App\Domain\Scheme\Exception\SchemeNotFoundException;
use App\Domain\SchemeGroup\Collection\SchemeGroupMap;
use App\Domain\SchemeGroup\Entity\SchemeGroup;
use App\Domain\SchemeGroup\Exception\SchemeGroupAlreadyExistsException;
use App\Domain\Shared\Exception\CriticalException;
use App\Domain\Shared\Exception\File\UnableToReadFileException;
use App\Domain\Shared\Exception\Json\UnableToDecodeJsonException;
use App\Domain\Shared\VO\Shared\NonEmptyStringVO;
use InvalidArgumentException;

final readonly class ReadSchemeGroupsListUseCase
{
    public function __construct(
        private ReadSchemeGroups                $readSchemeGroups,
        private SchemeGroupsListFormatValidator $schemeGroupsListFormatValidator,
        private ReadSchemesListUseCase          $readSchemesListUseCase,
    )
    {
    }

    /**
     * Read schemeGroups list from file
     *
     * @return SchemeGroupMap Map of schemeGroup entity
     *
     * @throws CriticalException
     */
    public function handle(): SchemeGroupMap
    {
        try {
            /**
             * Read schemeGroups
             */
            $rawSchemeGroupsList = $this->readSchemeGroups->read();


            /**
             * Validate schemeGroups
             */
            $this->schemeGroupsListFormatValidator->validate($rawSchemeGroupsList);


            /** @var array<array{name: string, schemes: string[]}> $rawSchemeGroupsList */

        } catch (UnableToReadFileException|UnableToDecodeJsonException|InvalidSchemeGroupListFormatException $e) {
            throw new CriticalException($e instanceof UnableToReadFileException
                ? "Unable to read scheme groups list"
                : "Invalid scheme groups list format",
                $e->getMessage()
            );
        }


        /**
         * Read schemes
         */
        $schemes = $this->readSchemesListUseCase->handle();


        /**
         * Create empty schemeGroups map
         */
        $schemeGroups = new SchemeGroupMap();

        foreach ($rawSchemeGroupsList as $rawSchemeGroup) {
            /**
             * Create empty SchemeGroup schemes map
             */
            $schemeGroupSchemes = new UniqueSchemesMap();

            foreach ($rawSchemeGroup['schemes'] as $rawSchemeGroupScheme) {
                /**
                 * Try to find scheme with specific id
                 */
                try {
                    $scheme = $schemes->getById($rawSchemeGroupScheme);
                } catch (SchemeNotFoundException) {
                    continue;
                    //TODO: Add reporter event
                }


                /**
                 * Try to add found scheme to SchemeGroup schemes map
                 */
                try {
                    $schemeGroupSchemes->add($scheme);
                } catch (SchemeAlreadyExistsException) {
                    continue;
                    //TODO: Add reporter event
                }
            }


            /**
             * Try to create new SchemeGroup and add it to schemeGroups map
             */
            try {
                $schemeGroups->add(
                    new SchemeGroup(
                        new NonEmptyStringVO($rawSchemeGroup['name']),
                        $schemeGroupSchemes)
                );
            } catch (SchemeGroupAlreadyExistsException|InvalidArgumentException) {
                continue;
                //TODO: Add reporter event
            }

        }

        return $schemeGroups;
    }
}