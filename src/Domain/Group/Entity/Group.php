<?php

declare(strict_types=1);

namespace App\Domain\Group\Entity;

use App\Domain\Outbound\Collection\UniqueTagOutboundsMap;
use App\Domain\Shared\VO\Shared\NonEmptyStringVO;

final class Group
{

    private readonly NonEmptyStringVO $name;

    private UniqueTagOutboundsMap $outbounds;

    /**
     * Constructor
     *
     * @param NonEmptyStringVO $name Group name
     * @param UniqueTagOutboundsMap $outbounds Outbounds
     */
    public function __construct(NonEmptyStringVO $name, UniqueTagOutboundsMap $outbounds)
    {
        /**
         * Set name
         */
        $this->name = $name;

        /**
         * Set outbounds ids
         */
        $this->outbounds = $outbounds;
    }


    /**
     * Get outbounds
     *
     * @return UniqueTagOutboundsMap Outbounds
     */
    public function getOutbounds(): UniqueTagOutboundsMap
    {
        return $this->outbounds;
    }

    /**
     * Set Outbound group outbounds
     *
     * @param UniqueTagOutboundsMap $outbounds Outbound group outbounds
     */
    public function setOutbounds(UniqueTagOutboundsMap $outbounds): void
    {
        $this->outbounds = $outbounds;
    }

    /**
     * Get outbounds group name as string
     *
     * @return string Group name string
     */
    public function getNameString(): string
    {
        return $this->name->getValue();
    }

    /**
     * Get outbounds group name VO
     *
     * @return NonEmptyStringVO Group name as VO
     */
    public function getName(): NonEmptyStringVO
    {
        return clone $this->name;
    }
}