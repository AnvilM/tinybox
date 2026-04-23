<?php

declare(strict_types=1);

namespace App\Domain\Group\Collection;

use App\Domain\Group\Entity\Group;
use App\Domain\Group\Exception\GroupAlreadyExistsException;
use App\Domain\Group\Exception\GroupNotFoundException;
use App\Domain\Shared\Exception\Json\UnableToEncodeJsonException;
use App\Domain\Shared\VO\Shared\NonEmptyStringVO;
use JsonException;
use Psl\Collection\MutableMap;
use Psl\Collection\MutableVector;

final readonly class GroupsMap
{
    /**
     * Group map <groupName: string, group: Group>
     *
     * @var MutableMap<string, Group>
     */
    private MutableMap $map;

    public function __construct()
    {
        $this->map = new MutableMap([]);
    }

    /**
     * Convert group map to JSON
     *
     * [{'name': 'sg1', 'schemes': ['scheme1', 'scheme2', ...]}, {'name': 'sg2', 'schemes': [...]}, ...]
     *
     * @return string JSON
     *
     * @throws UnableToEncodeJsonException If unable to encode json
     */
    public function toJson(): string
    {
        /**
         * Assert map is not empty
         */
        if ($this->map->isEmpty()) return '[]';


        $array = [];


        /**
         * Mapping map to array
         */
        foreach ($this->map as $group) {
            $array[] = [
                'name' => $group->getNameString(),
                'outbounds' => $group->getOutbounds()->getIds(),
            ];
        }


        /**
         * Try to encode JSON
         */
        try {
            return json_encode(
                $array,
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR
            );
        } catch (JsonException) {
            throw new UnableToEncodeJsonException();
        }
    }

    /**
     * Get group by name
     *
     * @param NonEmptyStringVO $name Group name
     *
     * @return Group Group
     *
     * @throws GroupNotFoundException If group not fund
     */
    public function getByName(NonEmptyStringVO $name): Group
    {
        /**
         * Get group
         */
        $group = $this->map->get($name->getValue());


        /**
         * Assert group exists
         */
        if ($group === null) throw new GroupNotFoundException();


        /**
         * Return group
         */
        return $group;
    }

    /**
     * Get group names
     *
     * @return MutableVector<string> Vector of group names
     */
    public function getGroupNames(): MutableVector
    {
        $groupNames = new MutableVector([]);

        foreach ($this->map as $group) {
            $groupNames->add($group->getNameString());
        }

        return $groupNames;
    }

    /**
     * Add group to map
     *
     * @param Group $group Group
     *
     * @throws GroupAlreadyExistsException If group already exists in map
     */
    public function add(Group $group): void
    {
        /**
         * Check if group name already exists
         */
        if ($this->containsGroup($group)) throw new GroupAlreadyExistsException();


        /**
         * Add group to map
         */
        $this->map->add($group->getNameString(), $group);
    }

    /**
     * Check group already exists in map
     *
     * @param Group $group Group
     *
     * @return bool Returns true if exists
     */
    public function containsGroup(Group $group): bool
    {
        return $this->map->containsKey($group->getNameString());
    }
}