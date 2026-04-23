<?php

declare(strict_types=1);

namespace App\Domain\Outbound\Collection;

use App\Domain\Interface\Outbound\OutboundSpecificationInterface;
use App\Domain\Outbound\Entity\Outbound;
use App\Domain\Outbound\Exception\OutboundAlreadyExistsException;
use App\Domain\Outbound\Exception\OutboundNotFoundException;
use App\Domain\Shared\Exception\Json\UnableToEncodeJsonException;
use JsonException;
use Psl\Collection\MutableMap;
use Psl\Collection\MutableVector;
use Psl\Collection\Vector;
use Psl\Collection\VectorInterface;

readonly class OutboundMap
{
    /**
     * Outbounds map <id: string, outbound: Outbound>
     *
     * @var MutableMap<string, Outbound>
     */
    protected MutableMap $outbounds;

    public function __construct()
    {
        $this->outbounds = new MutableMap([]);
    }

    /**
     * Get outbounds map as mutable vector of outbounds map split by chunks with provided size
     *
     * @param int $size Chunk size
     *
     * @return MutableVector<OutboundMap> Mutable vector of outbounds map split by chunks
     */
    public function getChunks(int $size): MutableVector
    {
        $chunkedMapVector = $this->outbounds->chunk($size);

        $chunkedOutboundsMapVector = new MutableVector([]);

        foreach ($chunkedMapVector as $mapChunk) {
            $outboundsMapChunk = new OutboundMap();
            foreach ($mapChunk as $outbound) {
                $outboundsMapChunk->add($outbound);
            }
            $chunkedOutboundsMapVector->add($outboundsMapChunk);
        }

        return $chunkedOutboundsMapVector;
    }

    /**
     * Add outbound to map
     *
     * @param Outbound $outbound Outbound
     *
     * @throws OutboundAlreadyExistsException If outbound already exists in map
     */
    public function add(Outbound $outbound): static
    {
        /**
         * Check if outbound name already exists
         */
        if ($this->containsOutbound($outbound)) throw new OutboundAlreadyExistsException();


        /**
         * Add outbound to map
         */
        $this->outbounds->add($outbound->getId(), $outbound);

        return $this;
    }

    /**
     * Check outbound already exists in map
     *
     * @param Outbound $outbound Outbound
     *
     * @return bool Returns true if exists
     */
    public function containsOutbound(Outbound $outbound): bool
    {
        foreach ($this->outbounds->toArray() as $outboundItem) {
            if ($outboundItem->equals($outbound)) return true;
        }
        return false;
    }

    /**
     * Convert outbounds map to array
     *
     * @return array Outbounds map as array
     */
    public function toArray(): array
    {
        $array = [];

        foreach ($this->outbounds as $outbound) {
            $array[] = $outbound->toArray();
        }

        return $array;
    }

    /**
     * Get outbound with id
     *
     * @param string $id Outbound id to search
     *
     * @return Outbound Found outbound
     *
     * @throws OutboundNotFoundException If outbound with provided id not found
     */
    public function getWithId(string $id): Outbound
    {
        return $this->outbounds->get($id) ?? throw new OutboundNotFoundException();
    }

    /**
     * Get outbounds ids
     *
     * @return Vector<string> Outbounds id's
     */
    public function getIds(): Vector
    {
        return new Vector($this->outbounds->keys()->toArray());
    }

    /**
     * @param VectorInterface<OutboundSpecificationInterface> $specificationVector
     *
     * @return OutboundMap Filtered map
     */
    public function filter(VectorInterface $specificationVector): OutboundMap
    {
        $filteredMap = clone $this;

        foreach ($filteredMap->getOutbounds() as $outbound) {
            foreach ($specificationVector as $specification) {
                /** @var OutboundSpecificationInterface $specification */
                if (!$specification->isSatisfiedBy($outbound)) {
                    $filteredMap->remove($outbound);
                    continue 2;
                }
            }
        }

        return $filteredMap;
    }

    /**
     * Get outbounds array
     *
     * @return Outbound[] Outbounds array
     */
    public function getOutbounds(): array
    {
        return $this->outbounds->toArray();
    }

    /**
     * Remove specified outbound from map
     *
     * @param Outbound $outbound Outbound to remove
     */
    public function remove(Outbound $outbound): void
    {
        $this->outbounds->remove($outbound->getId());
    }

    public function __clone(): void
    {
        $this->outbounds = new MutableMap($this->outbounds->toArray());
    }

    /**
     * Get outbounds tags vector
     *
     * @return MutableVector Tags vector
     */
    public function getTagsString(): MutableVector
    {
        $tags = MutableVector::default();

        foreach ($this->outbounds->toArray() as $outbound) {
            $tags->add($outbound->getTagString());
        }

        return $tags;
    }

    /**
     * Convert outbounds map to JSON
     *
     * @return string Outbounds map JSON: "id1" => Outbound, "id2" => ... OR empty array: []
     *
     * @throws UnableToEncodeJsonException If unable to encode outbounds map to json
     */
    public function toJson(): string
    {
        /**
         * Assert map is not empty
         */
        if ($this->outbounds->isEmpty()) return '[]';


        $array = [];


        /**
         * Mapping map to string array of raw outbounds strings
         */
        foreach ($this->outbounds as $outbound) {
            $array[] = $outbound->toArray();
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
     * Check if outbounds map is empty
     *
     * @return bool True if is empty
     */
    public function isEmpty(): bool
    {
        return $this->outbounds->isEmpty();
    }

    /**
     * Get the number of elements in the current map
     *
     * @return int The number of elements in the current map
     */
    public function count(): int
    {
        return $this->outbounds->count();
    }

    /**
     * Get current outbounds map
     *
     * @return MutableMap Outbounds map
     */
    public function getMap(): MutableMap
    {
        return $this->outbounds;
    }
}