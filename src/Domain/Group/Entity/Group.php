<?php

declare(strict_types=1);

namespace App\Domain\Group\Entity;

use App\Domain\Outbound\Collection\UniqueOutboundsMap;
use App\Domain\Shared\VO\Shared\NonEmptyStringVO;

final class Group
{

    private readonly NonEmptyStringVO $name;

    private UniqueOutboundsMap $outbounds;

    /**
     * Constructor
     *
     * @param NonEmptyStringVO $name Group name
     * @param UniqueOutboundsMap $outbounds Outbounds
     */
    public function __construct(NonEmptyStringVO $name, UniqueOutboundsMap $outbounds)
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
     * @return UniqueOutboundsMap Outbounds
     */
    public function getOutbounds(): UniqueOutboundsMap
    {
        return $this->outbounds;
    }

    /**
     * Set Outbound group outbounds
     *
     * @param UniqueOutboundsMap $outbounds Outbound group outbounds
     */
    public function setOutbounds(UniqueOutboundsMap $outbounds): void
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