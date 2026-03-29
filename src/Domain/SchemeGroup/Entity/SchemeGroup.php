<?php

declare(strict_types=1);

namespace App\Domain\SchemeGroup\Entity;

use App\Domain\Scheme\Collection\UniqueSchemesMap;
use App\Domain\Shared\VO\Shared\NonEmptyStringVO;

final class SchemeGroup
{

    private readonly NonEmptyStringVO $name;

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
     * Set Scheme group schemes
     *
     * @param UniqueSchemesMap $schemes Scheme group schemes
     */
    public function setSchemes(UniqueSchemesMap $schemes): void
    {
        $this->schemes = $schemes;
    }

    /**
     * Get schemes group name
     *
     * @return string SchemeGroup name
     */
    public function getName(): string
    {
        return $this->name->getValue();
    }

    /**
     * Get schemes group name VO
     *
     * @return NonEmptyStringVO SchemeGroup name as VO
     */
    public function getNameVO(): NonEmptyStringVO
    {
        return clone $this->name;
    }
}