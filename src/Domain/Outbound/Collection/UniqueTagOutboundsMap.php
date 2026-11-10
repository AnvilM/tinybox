<?php

declare(strict_types=1);

namespace App\Domain\Outbound\Collection;

use App\Domain\Outbound\Entity\Outbound;
use App\Domain\Outbound\Exception\OutboundAlreadyExistsException;
use App\Domain\Outbound\Exception\OutboundNotFoundException;
use Override;

readonly class UniqueTagOutboundsMap extends OutboundMap
{
    /**
     * Check outbound tag unique in map
     *
     * @param Outbound $outbound Outbound
     *
     * @return bool Returns true if not unique
     */
    #[Override]
    public function containsOutbound(Outbound $outbound): bool
    {
        return parent::containsOutbound($outbound) ||
            array_any(
                $this->outbounds->toArray(),
                fn($outboundItem) => $outboundItem->getTagString() === $outbound->getTagString()
            );
    }


    /**
     * Check if some outbound in map already has the tag
     *
     * @param string $tag Tag to search
     *
     * @return bool Returns true if tag is used
     */
    public function containsTag(string $tag): bool
    {
        return array_any(
            $this->outbounds->toArray(),
            fn($outboundItem) => $outboundItem->getTagString() === $tag
        );
    }

    /**
     * Get outbound with the same content (see {@see Outbound::equalsContent()}), whatever its tag is
     *
     * NOTE: The comparison is strict: all parameters of the outbounds must be equal, only the tag is ignored.
     *
     * @param Outbound $outbound Outbound to find duplicate
     *
     * @return Outbound|null Outbound if found, null if not found
     */
    public function getContentDuplicate(Outbound $outbound): ?Outbound
    {
        foreach ($this->outbounds->toArray() as $outboundItem) {
            if ($outboundItem->equalsContent($outbound)) return $outboundItem;
        }

        return null;
    }

    /**
     * Get the tag itself if it is free, otherwise the tag with the lowest free numeric suffix: "tag #2", "tag #3", ...
     *
     * @param string $tag Wanted tag
     *
     * @return string Tag that is not used in map
     */
    public function getUniqueTag(string $tag): string
    {
        if (!$this->containsTag($tag)) return $tag;

        for ($number = 2; ; $number++) {
            $candidate = $tag . ' #' . $number;

            if (!$this->containsTag($candidate)) return $candidate;
        }
    }

    /**
     * Add outbound to map, renaming it if its tag is already used by another outbound
     *
     * NOTE: Outbound with a busy tag gets the tag from {@see self::getUniqueTag()}, so it is not lost.
     *
     * @param Outbound $outbound Outbound to add
     *
     * @return Outbound Outbound that was added: the same object, or its copy with another tag
     *
     * @throws OutboundAlreadyExistsException If exactly the same outbound (content and tag) is already in map
     */
    public function addWithUniqueTag(Outbound $outbound): Outbound
    {
        if ($this->getDuplicate($outbound) !== null) throw new OutboundAlreadyExistsException($outbound);

        $uniqueTag = $this->getUniqueTag($outbound->getTagString());

        if ($uniqueTag !== $outbound->getTagString()) $outbound = $outbound->withTag($uniqueTag);

        $this->add($outbound);

        return $outbound;
    }

    /**
     * Get outbound with tag
     *
     * @param string $tag Outbound tag to search
     *
     * @return Outbound Found outbound
     *
     * @throws OutboundNotFoundException If outbound with provided id not found
     */
    public function getWithTag(string $tag): Outbound
    {
        foreach ($this->outbounds as $outbound) {
            if ($outbound->getTagString() === $tag) return $outbound;
        }

        throw new OutboundNotFoundException();
    }
}