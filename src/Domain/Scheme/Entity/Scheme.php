<?php

declare(strict_types=1);

namespace App\Domain\Scheme\Entity;

use App\Domain\Scheme\VO\SchemeTypeVO;
use App\Domain\Shared\VO\Shared\NonEmptyStringVO;
use InvalidArgumentException;
use Psl\Hash\Algorithm;

abstract readonly class Scheme
{
    private NonEmptyStringVO $tag;

    /**
     * @throws InvalidArgumentException Throws if given invalid data
     */
    public function __construct(
        ?NonEmptyStringVO $tag,
    )
    {
        $this->tag = $tag ?? new NonEmptyStringVO($this->generateTag());

    }

    protected abstract function generateTag(): string;

    public abstract function equals(Scheme $scheme): bool;

    public abstract function getType(): SchemeTypeVO;

    public function getTagString(): string
    {
        return $this->tag->getValue();
    }

    public function getTag(): NonEmptyStringVO
    {
        return clone $this->tag;
    }

    public function getHash(): string
    {
        return \Psl\Hash\hash($this->toRawScheme(), Algorithm::Murmur3F);
    }

    public abstract function toRawScheme(): string;

}