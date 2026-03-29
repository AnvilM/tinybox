<?php

declare(strict_types=1);

namespace App\Domain\Outbound\Entity;

use App\Domain\Outbound\VO\OutboundTypeVO;
use App\Domain\Shared\VO\Shared\NonEmptyStringVO;

abstract readonly class Outbound
{
    private NonEmptyStringVO $tag;


    public function __construct(NonEmptyStringVO $tag)
    {
        $this->tag = $tag;
    }


    /**
     * Convert outbound entity to array
     *
     * @return array Outbound entity as array
     */
    public abstract function toArray(): array;

    /**
     * Get outbound tag
     *
     * @return string Outbound tag
     */
    public function getTag(): string
    {
        return $this->tag->getValue();
    }

    /**
     * Get outbound type
     *
     * @return OutboundTypeVO Outbound type
     */
    public abstract function getType(): OutboundTypeVO;


    /**
     * Get outbound server, if outbound has no server field, e.g. direct outbound, return null
     *
     * @return string|null Outbound server or null if outbound has no server field, e.g. direct outbound
     */
    public abstract function getServer(): ?string;


    /**
     * Get outbound server port, if outbound has no server port field, e.g. direct outbound, return null
     *
     * @return int|null Outbound server port or null if outbound has no server port field, e.g. direct outbound
     */
    public abstract function getServerPort(): ?int;
}