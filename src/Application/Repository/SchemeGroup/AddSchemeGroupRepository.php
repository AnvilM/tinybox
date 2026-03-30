<?php

declare(strict_types=1);

namespace App\Application\Repository\SchemeGroup;

use App\Application\Exception\Repository\Shared\UnableToGetListException;
use App\Application\Repository\Scheme\GetSchemesListRepository;
use App\Application\Repository\SchemeGroup\Shared\File\ReadSchemeGroups;
use App\Application\Repository\SchemeGroup\Shared\File\WriteSchemeGroups;
use App\Application\Repository\SchemeGroup\Shared\SchemeGroupRepository;
use App\Application\Repository\SchemeGroup\Shared\Validator\SchemeGroupsListFormatValidator;
use App\Domain\SchemeGroup\Collection\SchemeGroupMap;
use App\Domain\SchemeGroup\Entity\SchemeGroup;
use App\Domain\SchemeGroup\Exception\SchemeGroupAlreadyExistsException;

final class AddSchemeGroupRepository extends SchemeGroupRepository
{
    public function __construct(ReadSchemeGroups $readSchemeGroups, SchemeGroupsListFormatValidator $schemeGroupsListFormatValidator, GetSchemesListRepository $getSchemesList, WriteSchemeGroups $writeSchemeGroups)
    {
        parent::__construct($readSchemeGroups, $schemeGroupsListFormatValidator, $getSchemesList, $writeSchemeGroups);
    }

    /**
     * Add scheme group to scheme groups list and save to file
     *
     * NOTE: Method doesn't write scheme groups list to file. Use method save
     *
     * @param SchemeGroup $schemeGroup Scheme group to add in scheme groups list
     *
     * @return SchemeGroupMap Current scheme groups list with added scheme group
     *
     * @throws UnableToGetListException If unable to get scheme groups list
     * @throws SchemeGroupAlreadyExistsException If scheme group already exist in scheme group list
     */
    public function add(SchemeGroup $schemeGroup): SchemeGroupMap
    {
        /**
         * Get scheme groups list
         */
        $schemeGroupsList = $this->getSchemeGroupsList();


        /**
         * Add scheme group to scheme groups list
         */
        $schemeGroupsList->add($schemeGroup);

        return $schemeGroupsList;
    }


    public function save(): SchemeGroupMap
    {
        return parent::save();
    }
}