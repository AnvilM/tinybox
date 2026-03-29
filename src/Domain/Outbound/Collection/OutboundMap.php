<?php

declare(strict_types=1);

namespace App\Domain\Outbound\Collection;

use App\Domain\Outbound\Entity\Outbound;
use App\Domain\Outbound\Exception\OutboundAlreadyExistsException;
use Psl\Collection\MutableMap;
use Psl\Collection\MutableVector;

final readonly class OutboundMap
{
    /**
     * Outbounds map <tag: string, outbound: Outbound>
     *
     * @var MutableMap<string, Outbound>
     */
    private MutableMap $map;

    public function __construct()
    {
        $this->map = new MutableMap([]);
    }

    /**
     * Get outbounds array
     *
     * @return Outbound[] Outbounds array
     */
    public function getOutbounds(): array
    {
        return $this->map->toArray();
    }

    /**
     * Convert outbounds map to array
     *
     * @return array Outbounds map as array
     */
    public function toArray(): array
    {
        $array = [];

        foreach ($this->map as $outbound) {
            $array[] = $outbound->toArray();
        }

        return $array;
    }

    /**
     * Remove outbound with specified tag from map
     *
     * @param string $tag Outbound tag to remove
     */
    public function removeWithTag(string $tag): void
    {
        $this->map->remove($tag);
    }

    /**
     * Check if outbounds map is empty
     *
     * @return bool True if is empty
     */
    public function isEmpty(): bool
    {
        return $this->map->isEmpty();
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
        $chunkedMapVector = $this->map->chunk($size);

        $chunkedOutboundsMapVector = new MutableVector([]);

        foreach ($chunkedMapVector as $mapChunk) {
            $outboundsMapChunk = new OutboundMap();
            foreach ($mapChunk as $outboundTag => $outbound) {
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
    public function add(Outbound $outbound): void
    {
        /**
         * Check if outbound name already exists
         */
        if ($this->containsOutbound($outbound)) throw new OutboundAlreadyExistsException();


        /**
         * Add outbound to map
         */
        $this->map->add($outbound->getTag(), $outbound);
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
        return $this->map->containsKey($outbound->getTag());
    }
}