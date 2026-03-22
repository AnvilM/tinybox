<?php

declare(strict_types=1);

namespace App\Domain\SchemeGroup\Collection;

use App\Domain\SchemeGroup\Entity\SchemeGroup;
use App\Domain\SchemeGroup\Exception\SchemeGroupAlreadyExistsException;
use App\Domain\SchemeGroup\Exception\SchemeGroupNotFoundException;
use App\Domain\Shared\Exception\Json\UnableToEncodeJsonException;
use App\Domain\Shared\VO\Shared\NonEmptyStringVO;
use JsonException;
use Psl\Collection\MutableMap;
use Psl\Collection\MutableVector;

final readonly class SchemeGroupMap
{
    /**
     * Scheme group map <schemeGroupName: string, schemeGroup: SchemeGroup>
     *
     * @var MutableMap<string, SchemeGroup>
     */
    private MutableMap $map;

    public function __construct()
    {
        $this->map = new MutableMap([]);
    }

    /**
     * Convert scheme group map to JSON
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
        foreach ($this->map as $schemeGroup) {
            $array[] = [
                'name' => $schemeGroup->getName(),
                'schemes' => $schemeGroup->getSchemes()->getIds()->toArray(),
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
     * Get scheme group by name
     *
     * @param NonEmptyStringVO $name Scheme group name
     *
     * @return SchemeGroup Scheme group
     *
     * @throws SchemeGroupNotFoundException If scheme group not fund
     */
    public function getByName(NonEmptyStringVO $name): SchemeGroup
    {
        /**
         * Get scheme group
         */
        $schemeGroup = $this->map->get($name->getValue());


        /**
         * Assert scheme group exists
         */
        if ($schemeGroup === null) throw new SchemeGroupNotFoundException();


        /**
         * Return scheme group
         */
        return $schemeGroup;
    }

    /**
     * Get scheme group names
     *
     * @return MutableVector<string> Vector of schemeGroup names
     */
    public function getSchemeGroupNames(): MutableVector
    {
        $schemeGroupNames = new MutableVector([]);

        foreach ($this->map as $schemeGroup) {
            $schemeGroupNames->add($schemeGroup->getName());
        }

        return $schemeGroupNames;
    }

    /**
     * Add scheme group to map
     *
     * @param SchemeGroup $schemeGroup Scheme group
     *
     * @throws SchemeGroupAlreadyExistsException If scheme group already exists in map
     */
    public function add(SchemeGroup $schemeGroup): void
    {
        /**
         * Check if scheme group name already exists
         */
        if ($this->containsSchemeGroup($schemeGroup)) throw new SchemeGroupAlreadyExistsException();


        /**
         * Add scheme group to map
         */
        $this->map->add($schemeGroup->getName(), $schemeGroup);
    }

    /**
     * Check scheme group already exists in map
     *
     * @param SchemeGroup $schemeGroup Scheme group
     *
     * @return bool Returns true if exists
     */
    public function containsSchemeGroup(SchemeGroup $schemeGroup): bool
    {
        return $this->map->containsKey($schemeGroup->getName());
    }
}