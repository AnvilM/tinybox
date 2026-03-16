<?php

declare(strict_types=1);

namespace App\Domain\Scheme\Collection;

use App\Domain\Scheme\Entity\Scheme;
use App\Domain\Scheme\Exception\SchemeAlreadyExistsException;
use Override;

final class UniqueSchemesMap extends SchemeMap
{
    #[Override]
    public function add(Scheme $scheme): void
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
}