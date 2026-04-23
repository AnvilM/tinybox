<?php

declare(strict_types=1);

namespace App\Application\Repository\Group;

use App\Application\Exception\Repository\Shared\UnableToGetListException;
use App\Application\Repository\Group\Shared\File\ReadGroups;
use App\Application\Repository\Group\Shared\File\WriteGroups;
use App\Application\Repository\Group\Shared\GroupRepository;
use App\Application\Repository\Group\Shared\Validator\GroupsListFormatValidator;
use App\Application\Repository\Outbound\GetOutboundsListRepository;
use App\Domain\Group\Collection\GroupsMap;
use App\Domain\Group\Entity\Group;
use App\Domain\Group\Exception\GroupAlreadyExistsException;

final class AddGroupRepository extends GroupRepository
{
    public function __construct(ReadGroups $readGroups, GroupsListFormatValidator $groupsListFormatValidator, GetOutboundsListRepository $getOutboundsListRepository, WriteGroups $writeGroups)
    {
        parent::__construct($readGroups, $groupsListFormatValidator, $getOutboundsListRepository, $writeGroups);
    }

    /**
     * Add group to groups list and save to file
     *
     * NOTE: Method doesn't write groups list to file. Use method save
     *
     * @param Group $group Group to add in groups list
     *
     * @return GroupsMap Current groups list with added group
     *
     * @throws UnableToGetListException If unable to get groups list
     * @throws GroupAlreadyExistsException If group already exist in group list
     */
    public function add(Group $group): GroupsMap
    {
        /**
         * Get groups list
         */
        $groupsList = $this->getGroupsList();


        /**
         * Add group to groups list
         */
        $groupsList->add($group);

        return $groupsList;
    }


    public function save(): GroupsMap
    {
        return parent::save();
    }
}