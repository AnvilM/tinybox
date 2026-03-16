<?php

declare(strict_types=1);

namespace App\Domain\Subscription\VO;

use App\Domain\Subscription\Exception\InvalidSubscriptionNameException;
use Psl\Type\Exception\CoercionException;
use function Psl\Type\non_empty_string;

final readonly class SubscriptionNameVO
{
    private string $name;

    /**
     * Constructor
     *
     * @param string $name Subscription name
     *
     * @throws InvalidSubscriptionNameException If provided name is invalid
     */
    public function __construct(string $name)
    {
        try {
            $this->name = non_empty_string()->coerce($name);
        } catch (CoercionException) {
            throw new InvalidSubscriptionNameException();
        }
    }


    /**
     * Get subscription name
     *
     * @return string Subscription name
     */
    public function getName(): string
    {
        return $this->name;
    }
}