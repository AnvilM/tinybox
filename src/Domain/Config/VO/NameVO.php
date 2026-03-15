<?php

declare(strict_types=1);

namespace App\Domain\Config\VO;

use App\Domain\Config\Exception\InvalidConfigNameException;
use Psl\Type\Exception\CoercionException;
use function Psl\Type\non_empty_string;

final readonly class NameVO
{
    private string $name;

    /**
     * Constructor
     *
     * @param string $name Config name
     *
     * @throws InvalidConfigNameException
     */
    public function __construct(string $name)
    {
        try {
            $this->name = non_empty_string()->coerce($name);
        } catch (CoercionException) {
            throw new InvalidConfigNameException();
        }
    }

    public function getName(): string
    {
        return $this->name;
    }
}