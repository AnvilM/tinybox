<?php

declare(strict_types=1);

namespace App\Domain\Config\Entity;

use App\Domain\Config\VO\NameVO;
use App\Domain\Config\VO\SchemesIdsVO;

final readonly class Config
{

    private NameVO $name;

    private SchemesIdsVO $schemesIds;

    /**
     * Constructor
     *
     * @param NameVO $name Config name
     * @param SchemesIdsVO $schemesIds Schemes ids
     */
    public function __construct(NameVO $name, SchemesIdsVO $schemesIds)
    {
        /**
         * Set name
         */
        $this->name = $name;

        /**
         * Set schemes ids
         */
        $this->schemesIds = $schemesIds;
    }


    /**
     * Get schemes ids value object
     *
     * @return SchemesIdsVO Schemes ids value object
     */
    public function getSchemesIds(): SchemesIdsVO
    {
        return $this->schemesIds;
    }

    /**
     * Get config name
     *
     * @return NameVO Config name
     */
    public function getName(): NameVO
    {
        return $this->name;
    }
}