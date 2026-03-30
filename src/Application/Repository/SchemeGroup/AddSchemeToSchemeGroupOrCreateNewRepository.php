<?php

declare(strict_types=1);

namespace App\Application\Repository\SchemeGroup;

use App\Application\Exception\Repository\Shared\UnableToGetListException;
use App\Application\Repository\Scheme\GetSchemesListRepository;
use App\Application\Repository\SchemeGroup\Shared\File\ReadSchemeGroups;
use App\Application\Repository\SchemeGroup\Shared\File\WriteSchemeGroups;
use App\Application\Repository\SchemeGroup\Shared\SchemeGroupRepository;
use App\Application\Repository\SchemeGroup\Shared\Validator\SchemeGroupsListFormatValidator;
use App\Domain\Scheme\Collection\UniqueSchemesMap;
use App\Domain\Scheme\Entity\Scheme;
use App\Domain\Scheme\Exception\SchemeAlreadyExistsException;
use App\Domain\SchemeGroup\Entity\SchemeGroup;
use App\Domain\SchemeGroup\Exception\SchemeGroupNotFoundException;
use App\Domain\Shared\VO\Shared\NonEmptyStringVO;

final class AddSchemeToSchemeGroupOrCreateNewRepository extends SchemeGroupRepository
{

    public function __construct(ReadSchemeGroups $readSchemeGroups, SchemeGroupsListFormatValidator $schemeGroupsListFormatValidator, GetSchemesListRepository $getSchemesList, WriteSchemeGroups $writeSchemeGroups)
    {
        parent::__construct($readSchemeGroups, $schemeGroupsListFormatValidator, $getSchemesList, $writeSchemeGroups);
    }

    /**
     * Add scheme to scheme group
     *
     * NOTE: Method doesn't write scheme group list to file. Use method save
     *
     * @param NonEmptyStringVO $schemeGroupName Scheme group name
     * @param Scheme $scheme Scheme to add in scheme group
     *
     * @return SchemeGroup Current scheme group with added scheme
     *
     * @throws SchemeAlreadyExistsException If scheme already exist in scheme group with provided name
     * @throws UnableToGetListException If unable to get scheme groups list
     */
    public function add(NonEmptyStringVO $schemeGroupName, Scheme $scheme): SchemeGroup
    {
        /**
         * Get list of all scheme groups
         */
        $schemeGroupsList = $this->getSchemeGroupsList();


        /**
         * Find scheme group with provided name
         */
        try {
            $schemeGroup = $schemeGroupsList->getByName($schemeGroupName);
        } catch (SchemeGroupNotFoundException) {
            /**
             * Create new scheme group and add provided scheme
             */
            $newSchemeGroup = new SchemeGroup($schemeGroupName, new UniqueSchemesMap()->add($scheme));


            /**
             * Add new scheme group to scheme groups list
             */
            $this->getSchemeGroupsList()->add($newSchemeGroup);

            return $newSchemeGroup;
        }


        /**
         * Add scheme to found scheme group
         */
        $schemeGroup->getSchemes()->add($scheme);


        return $schemeGroup;
    }
}