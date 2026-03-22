<?php

declare(strict_types=1);

namespace App\Domain\SchemeGroup\Entity;

use App\Domain\Scheme\Collection\UniqueSchemesMap;
use App\Domain\Shared\VO\Shared\NonEmptyStringVO;

final readonly class SchemeGroup
{

    private NonEmptyStringVO $name;

    private UniqueSchemesMap $schemes;

    /**
     * Constructor
     *
     * @param NonEmptyStringVO $name SchemeGroup name
     * @param UniqueSchemesMap $schemes Schemes
     */
    public function __construct(NonEmptyStringVO $name, UniqueSchemesMap $schemes)
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
     * @return string SchemeGroup name
     */
    public function getName(): string
    {
        return $this->name->getValue();
    }
}