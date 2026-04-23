<?php

declare(strict_types=1);

namespace App\Application\Repository\Group\Shared;

use App\Application\Exception\Repository\Group\Validator\InvalidGroupListFormatException;
use App\Application\Exception\Repository\Shared\UnableToGetListException;
use App\Application\Exception\Repository\Shared\UnableToSaveListException;
use App\Application\Repository\Group\Shared\File\ReadGroups;
use App\Application\Repository\Group\Shared\File\WriteGroups;
use App\Application\Repository\Group\Shared\Validator\GroupsListFormatValidator;
use App\Application\Repository\Outbound\GetOutboundsListRepository;
use App\Domain\Group\Collection\GroupsMap;
use App\Domain\Group\Entity\Group;
use App\Domain\Group\Exception\GroupAlreadyExistsException;
use App\Domain\Outbound\Collection\UniqueOutboundsMap;
use App\Domain\Outbound\Exception\OutboundAlreadyExistsException;
use App\Domain\Outbound\Exception\OutboundNotFoundException;
use App\Domain\Shared\Exception\File\UnableToReadFileException;
use App\Domain\Shared\Exception\File\UnableToSaveFileException;
use App\Domain\Shared\Exception\Json\UnableToDecodeJsonException;
use App\Domain\Shared\Exception\Json\UnableToEncodeJsonException;
use App\Domain\Shared\VO\Shared\NonEmptyStringVO;
use InvalidArgumentException;

class GroupRepository
{
    private static ?GroupsMap $groupsMap = null;


    public function __construct(
        private readonly ReadGroups                 $readGroups,
        private readonly GroupsListFormatValidator  $groupsListFormatValidator,
        private readonly GetOutboundsListRepository $getOutboundsListRepository,
        protected readonly WriteGroups              $writeGroups,
    )
    {
    }


    /**
     * Get map of all groups
     *
     * @return GroupsMap Group map
     *
     * @throws UnableToGetListException If unable to read file or groups file is invalid format
     */
    protected function getGroupsList(): GroupsMap
    {
        if (self::$groupsMap !== null) return self::$groupsMap;

        try {
            /**
             * Read groups
             */
            $rawGroupsList = $this->readGroups->read();


            /**
             * Validate groups
             */
            $this->groupsListFormatValidator->validate($rawGroupsList);


            /** @var array<array{name: string, groups: string[]}> $rawGroupsList */

        } catch (UnableToReadFileException|UnableToDecodeJsonException|InvalidGroupListFormatException $e) {
            throw new UnableToGetListException($e instanceof UnableToReadFileException
                ? "Unable to read groups list"
                : "Invalid groups list format",
                $e->getMessage()
            );
        }


        /**
         * Try to get outbounds list
         */
        try {
            $outbounds = $this->getOutboundsListRepository->getOutboundsList();
        } catch (UnableToGetListException $e) {
            throw new UnableToGetListException($e->getMessage(), $e->getDebugMessage());
        }


        /**
         * Create empty groups map
         */
        $groupsMap = new GroupsMap();

        foreach ($rawGroupsList as $rawGroup) {
            /**
             * Create empty outbounds map
             */
            $groupOutbounds = new UniqueOutboundsMap();

            foreach ($rawGroup['outbounds'] as $outboundId) {
                /**
                 * Try to find outbound with specific id
                 */
                try {
                    $outbound = $outbounds->getWithId($outboundId);
                } catch (OutboundNotFoundException) {
                    continue;
                    //TODO: Add reporter event
                }


                /**
                 * Try to add found outbound to group
                 */
                try {
                    $groupOutbounds->add($outbound);
                } catch (OutboundAlreadyExistsException) {
                    continue;
                    //TODO: Add reporter event
                }
            }


            /**
             * Try to create new Group and add it to groups map
             */
            try {
                $groupsMap->add(
                    new Group(
                        new NonEmptyStringVO($rawGroup['name']),
                        $groupOutbounds)
                );
            } catch (GroupAlreadyExistsException|InvalidArgumentException) {
                continue;
                //TODO: Add reporter event
            }

        }


        /**
         * Update groups map
         */
        self::$groupsMap = $groupsMap;

        return $groupsMap;
    }


    /**
     * Save current groups list to file
     *
     * @throws UnableToSaveListException If unable to write file, or no group loaded
     */
    protected function save(): GroupsMap
    {
        if (self::$groupsMap === null) throw new UnableToSaveListException(
            "No groups list available"
        );

        try {
            $this->writeGroups->write(self::$groupsMap);
        } catch (UnableToSaveFileException|UnableToEncodeJsonException $e) {
            throw new UnableToSaveListException($e->getMessage(), $e->getDebugMessage());
        }

        return self::$groupsMap;
    }
}