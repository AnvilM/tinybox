<?php

declare(strict_types=1);

namespace App\Domain\Shared\VO\Shared;

use App\Domain\Scheme\Exception\SchemeAlreadyExistsException;
use Psl\Collection\MutableVector;

final readonly class SchemesIdsVO
{


    /** @var MutableVector<string> $schemes Schemes ids vector */
    private MutableVector $schemes;


    public function __construct()
    {

        /**
         * Create new mutable vector
         */
        $this->schemes = new MutableVector([]);
    }


    /**
     * Add scheme id to vector
     *
     * @param SchemeIdVO $schemeId SchemeId value object
     *
     * @throws SchemeAlreadyExistsException
     */
    public function add(SchemeIdVO $schemeId): self
    {
        /**
         * Assert id isn't already exists
         */
        if ($this->schemes->linearSearch($schemeId->getSchemeId()) !== null) throw new SchemeAlreadyExistsException();


        /**
         * Add scheme id to vector
         */
        $this->schemes->add($schemeId->getSchemeId());


        return $this;
    }


    /**
     * Get schemes ids as array
     *
     * @return string[] Schemes ids array
     */
    public function getSchemesIdsArray(): array
    {
        return $this->schemes->toArray();
    }
}