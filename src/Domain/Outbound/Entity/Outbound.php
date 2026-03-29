<?php

declare(strict_types=1);

namespace App\Domain\Outbound\Entity;

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
     * @return string Outbound type
     */
    protected abstract function getType(): string;
}