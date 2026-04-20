<?php

declare(strict_types=1);

namespace App\Application\Repository\Group;

use App\Application\Repository\Group\Shared\File\ReadGroups;
use App\Application\Repository\Group\Shared\File\WriteGroups;
use App\Application\Repository\Group\Shared\GroupRepository;
use App\Application\Repository\Group\Shared\Validator\GroupsListFormatValidator;
use App\Application\Repository\Outbound\GetOutboundsListRepository;
use App\Domain\Group\Collection\GroupsMap;

final class GetGroupListRepository extends GroupRepository
{
    public function __construct(ReadGroups $readGroups, GroupsListFormatValidator $groupsListFormatValidator, GetOutboundsListRepository $getOutboundsListRepository, WriteGroups $writeGroups)
    {
        parent::__construct($readGroups, $groupsListFormatValidator, $getOutboundsListRepository, $writeGroups);
    }


    /**
     * @inheritdoc
     */
    public function getGroupsList(): GroupsMap
    {
        return parent::getGroupsList();
    }
}