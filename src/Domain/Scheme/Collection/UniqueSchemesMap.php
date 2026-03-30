<?php

declare(strict_types=1);

namespace App\Domain\Scheme\Collection;

use App\Domain\Scheme\Entity\Scheme;
use App\Domain\Scheme\Exception\SchemeAlreadyExistsException;
use App\Domain\Scheme\Exception\SchemeNotFoundException;
use Override;

final class UniqueSchemesMap extends SchemeMap
{
    /**
     * Add scheme to map
     *
     * @param Scheme $scheme Scheme
     *
     * @throws SchemeAlreadyExistsException If scheme already exists in map
     */
    #[Override]
    public function add(Scheme $scheme): static
    {
        /**
         * Check scheme already exists
         */
        if ($this->containsSchemeWithTag($scheme)) {
            throw new SchemeAlreadyExistsException();
        }

        /**
         * Add scheme to map
         */
        $this->map->add($scheme->getHash(), $scheme);

        return $this;
    }

    /**
     * Check scheme contains provided scheme
     *
     * @param Scheme $scheme Scheme
     *
     * @return bool Returns true if map contains provided scheme
     */
    private function containsSchemeWithTag(Scheme $scheme): bool
    {
        foreach ($this->map as $schemeItem) {
            return (
                $schemeItem->equals($scheme)
                || $schemeItem->getTag() === $scheme->getTag()
            );
        }

        return false;
    }


    /**
     * Get scheme with provided tag
     *
     * @param string $tag Tag
     * @return Scheme Scheme
     *
     * @throws SchemeNotFoundException If scheme with provided tag does not exist
     */
    public function getByTag(string $tag): Scheme
    {
        foreach ($this->map as $scheme) {
            if ($scheme->getTag() === $tag) {
                return $scheme;
            }
        }

        throw new SchemeNotFoundException();
    }
}