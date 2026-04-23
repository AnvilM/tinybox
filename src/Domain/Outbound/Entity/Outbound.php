<?php

declare(strict_types=1);

namespace App\Domain\Outbound\Entity;

use App\Domain\Interface\Shared\Equable;
use App\Domain\Outbound\VO\OutboundTypeVO;
use App\Domain\Shared\Trait\ComparesNullable;
use App\Domain\Shared\VO\Shared\NonEmptyStringVO;
use Psl\Hash\Algorithm;

abstract readonly class Outbound implements Equable
{
    use ComparesNullable;

    private NonEmptyStringVO $tag;

    public function __construct(NonEmptyStringVO $tag)
    {
        $this->tag = $tag;
    }

    /**
     * Get outbound tag as string
     *
     * @return string Outbound tag as sting
     */
    public function getTagString(): string
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

    /**
     * Get outbound tag as NonEmptyString object
     *
     * @return NonEmptyStringVO Tag as NonEmptyString object
     */
    public function getTag(): NonEmptyStringVO
    {
        return $this->tag;
    }

    
    /**
     * Check if other object is equals to current
     *
     * @param mixed $other Other object
     *
     * @return bool True if equals
     */
    public abstract function equals(mixed $other): bool;

    /**
     * Get outbound id
     *
     * @return string Outbound id
     */
    public function getId(): string
    {
        return \Psl\Hash\hash(
            json_encode($this->toArray()),
            Algorithm::Murmur3F
        );
    }

    /**
     * Convert outbound entity to array
     *
     * @return array Outbound entity as array
     */
    public abstract function toArray(): array;

}