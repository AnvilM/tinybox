<?php

declare(strict_types=1);

namespace App\Application\Repository\Group;

use App\Application\Exception\Repository\Shared\UnableToGetListException;
use App\Application\Repository\Group\Shared\File\ReadGroups;
use App\Application\Repository\Group\Shared\File\WriteGroups;
use App\Application\Repository\Group\Shared\GroupRepository;
use App\Application\Repository\Group\Shared\Validator\GroupsListFormatValidator;
use App\Application\Repository\Outbound\GetOutboundsListRepository;
use App\Domain\Group\Entity\Group;
use App\Domain\Group\Exception\GroupNotFoundException;
use App\Domain\Outbound\Collection\UniqueOutboundsMap;
use App\Domain\Outbound\Entity\Outbound;
use App\Domain\Outbound\Exception\OutboundAlreadyExistsException;
use App\Domain\Shared\VO\Shared\NonEmptyStringVO;

final class AddOutboundToGroupOrCreateNewRepository extends GroupRepository
{

    public function __construct(ReadGroups $readGroups, GroupsListFormatValidator $groupsListFormatValidator, GetOutboundsListRepository $getOutboundsListRepository, WriteGroups $writeGroups)
    {
        parent::__construct($readGroups, $groupsListFormatValidator, $getOutboundsListRepository, $writeGroups);
    }

    /**
     * Add outbound to group
     *
     * NOTE: Method doesn't write group list to file. Use method save
     *
     * @param NonEmptyStringVO $groupName Group name
     * @param Outbound $outbound Outbound to add in group
     *
     * @return Group Current group with added outbound
     *
     * @throws OutboundAlreadyExistsException If outbound already exist in group with provided name
     * @throws UnableToGetListException If unable to get groups list
     */
    public function add(NonEmptyStringVO $groupName, Outbound $outbound): Group
    {
        /**
         * Get list of all groups
         */
        $groupsList = $this->getGroupsList();


        /**
         * Find group with provided name
         */
        try {
            $group = $groupsList->getByName($groupName);
        } catch (GroupNotFoundException) {
            /**
             * Create new group and add provided outbound
             */
            $newGroup = new Group($groupName, new UniqueOutboundsMap()->add($outbound));


            /**
             * Add new group to groups list
             */
            $this->getGroupsList()->add($newGroup);

            return $newGroup;
        }


        /**
         * Add outbound to found group
         */
        $group->getOutbounds()->add($outbound);


        return $group;
    }
}