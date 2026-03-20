<?php

declare(strict_types=1);

namespace App\Domain\Outbound\Collection;

use App\Domain\Outbound\Entity\Outbound;
use App\Domain\Outbound\Exception\OutboundAlreadyExistsException;
use Psl\Collection\MutableMap;

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

}