<?php

declare(strict_types=1);

namespace App\Application\Repository\SchemeGroup\Shared;

use App\Application\Exception\Repository\Scheme\UnableToGetSchemesListException;
use App\Application\Exception\Repository\SchemeGroup\Validator\InvalidSchemeGroupListFormatException;
use App\Application\Exception\Repository\Shared\UnableToGetListException;
use App\Application\Exception\Repository\Shared\UnableToSaveListException;
use App\Application\Repository\Scheme\GetSchemesListRepository;
use App\Application\Repository\SchemeGroup\Shared\File\ReadSchemeGroups;
use App\Application\Repository\SchemeGroup\Shared\File\WriteSchemeGroups;
use App\Application\Repository\SchemeGroup\Shared\Validator\SchemeGroupsListFormatValidator;
use App\Domain\Scheme\Collection\UniqueSchemesMap;
use App\Domain\Scheme\Exception\SchemeAlreadyExistsException;
use App\Domain\Scheme\Exception\SchemeNotFoundException;
use App\Domain\SchemeGroup\Collection\SchemeGroupMap;
use App\Domain\SchemeGroup\Entity\SchemeGroup;
use App\Domain\SchemeGroup\Exception\SchemeGroupAlreadyExistsException;
use App\Domain\Shared\Exception\File\UnableToReadFileException;
use App\Domain\Shared\Exception\File\UnableToSaveFileException;
use App\Domain\Shared\Exception\Json\UnableToDecodeJsonException;
use App\Domain\Shared\Exception\Json\UnableToEncodeJsonException;
use App\Domain\Shared\VO\Shared\NonEmptyStringVO;
use InvalidArgumentException;

class SchemeGroupRepository
{
    private static ?SchemeGroupMap $schemeGroupMap = null;


    public function __construct(
        private readonly ReadSchemeGroups                $readSchemeGroups,
        private readonly SchemeGroupsListFormatValidator $schemeGroupsListFormatValidator,
        private readonly GetSchemesListRepository        $getSchemesList,
        protected readonly WriteSchemeGroups             $writeSchemeGroups,
    )
    {
    }


    /**
     * Get map of all scheme groups
     *
     * @return SchemeGroupMap Scheme group map
     *
     * @throws UnableToGetListException If unable to read file or schemes file is invalid format
     */
    protected function getSchemeGroupsList(): SchemeGroupMap
    {
        if (self::$schemeGroupMap !== null) return self::$schemeGroupMap;

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
            throw new UnableToGetListException($e instanceof UnableToReadFileException
                ? "Unable to read scheme groups list"
                : "Invalid scheme groups list format",
                $e->getMessage()
            );
        }


        /**
         * Try to get schemes list
         */
        try {
            $schemes = $this->getSchemesList->getSchemesList();
        } catch (UnableToGetSchemesListException $e) {
            throw new UnableToGetListException($e->getMessage(), $e->getDebugMessage());
        }


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


        /**
         * Update scheme groups map
         */
        self::$schemeGroupMap = $schemeGroups;

        return $schemeGroups;
    }


    /**
     * Save current scheme group list to file
     *
     * @throws UnableToSaveListException If unable to write file, or no scheme group loaded
     */
    protected function save(): SchemeGroupMap
    {
        if (self::$schemeGroupMap === null) throw new UnableToSaveListException(
            "No scheme groups list available"
        );

        try {
            $this->writeSchemeGroups->write(self::$schemeGroupMap);
        } catch (UnableToSaveFileException|UnableToEncodeJsonException $e) {
            throw new UnableToSaveListException($e->getMessage(), $e->getDebugMessage());
        }

        return self::$schemeGroupMap;
    }
}