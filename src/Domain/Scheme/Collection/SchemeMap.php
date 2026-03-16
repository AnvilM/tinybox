<?php

declare(strict_types=1);

namespace App\Domain\Scheme\Collection;

use App\Domain\Scheme\Entity\Scheme;
use App\Domain\Scheme\Exception\SchemeAlreadyExistsException;
use App\Domain\Shared\Exception\Json\UnableToEncodeJsonException;
use JsonException;
use Psl\Collection\MutableMap;
use Psl\Collection\MutableVector;

class SchemeMap
{
    /**
     * @var MutableMap<string, Scheme> $map Schemes map
     */
    protected MutableMap $map;


    public function __construct()
    {
        $this->map = new MutableMap([]);
    }


    /**
     * Add scheme to map
     *
     * @param Scheme $scheme Scheme
     *
     * @throws SchemeAlreadyExistsException If scheme already exists in map
     */
    public function add(Scheme $scheme): void
    {
        /**
         * Check scheme already exists
         */
        if ($this->containsScheme($scheme)) {
            throw new SchemeAlreadyExistsException();
        }


        /**
         * Add scheme to map
         */
        $this->map->add($scheme->getHash(), $scheme);
    }


    /**
     * Check scheme exists in map
     *
     * @param Scheme $scheme Scheme
     *
     * @return bool Returns true if exists
     */
    public function containsScheme(Scheme $scheme): bool
    {
        foreach ($this->map as $schemeItem) {
            if ($schemeItem->equals($scheme)) return true;
        }

        return false;
    }


    /**
     * Check scheme with provided id exists in map
     *
     * @param string $schemeId Scheme id
     *
     * @return bool Returns true if exists
     */
    public function containsSchemeId(string $schemeId): bool
    {
        return $this->map->containsKey($schemeId);
    }


    /**
     * Convert scheme map to json
     *
     * @return string Scheme map JSON: "hash" => "RawSchemeString", "hash2" => ... OR empty array: []
     *
     * @throws UnableToEncodeJsonException If unable to encode scheme map to json
     */
    public function toJson(): string
    {
        /**
         * Assert map is not empty
         */
        if ($this->map->isEmpty()) return '[]';


        $array = [];


        /**
         * Mapping map to string array of raw schemes strings
         */
        foreach ($this->map as $scheme) {
            $array[] = $scheme->toRawScheme();
        }

        /**
         * Try to convert array to JSON
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
     * @return MutableMap<string, Scheme> Schemes map
     */
    public function getMap(): MutableMap
    {
        return clone $this->map;
    }


    /**
     * @return MutableVector<string> Mutable vector of schemes ids e.g., ["id1", "id2"]
     */
    public function getIds(): MutableVector
    {
        return $this->map->keys();
    }
}