<?php

declare(strict_types=1);

namespace App\Domain\Config\Entity;

use App\Domain\Config\VO\ConfigNameVO;
use App\Domain\Shared\VO\Shared\SchemesIdsVO;

final readonly class Config
{

    private ConfigNameVO $name;

    private SchemesIdsVO $schemesIds;

    /**
     * Constructor
     *
     * @param ConfigNameVO $name Config name
     * @param SchemesIdsVO $schemesIds Schemes ids
     */
    public function __construct(ConfigNameVO $name, SchemesIdsVO $schemesIds)
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
     * @return string Config name
     */
    public function getName(): string
    {
        return $this->name->getName();
    }
}