<?php

declare(strict_types=1);

namespace App\Domain\Config\Entity;

use App\Domain\Config\VO\ConfigNameVO;
use App\Domain\Scheme\Collection\UniqueSchemesMap;

final readonly class Config
{

    private ConfigNameVO $name;

    private UniqueSchemesMap $schemes;

    /**
     * Constructor
     *
     * @param ConfigNameVO $name Config name
     * @param UniqueSchemesMap $schemes Schemes
     */
    public function __construct(ConfigNameVO $name, UniqueSchemesMap $schemes)
    {
        /**
         * Set name
         */
        $this->name = $name;

        /**
         * Set schemes ids
         */
        $this->schemes = $schemes;
    }


    /**
     * Get schemes
     *
     * @return UniqueSchemesMap Schemes
     */
    public function getSchemes(): UniqueSchemesMap
    {
        return $this->schemes;
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