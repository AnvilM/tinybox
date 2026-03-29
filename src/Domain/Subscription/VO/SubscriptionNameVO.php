<?php

declare(strict_types=1);

namespace App\Domain\Subscription\VO;

use App\Domain\Shared\VO\Shared\NonEmptyStringVO;
use App\Domain\Subscription\Exception\InvalidSubscriptionNameException;
use InvalidArgumentException;

final readonly class SubscriptionNameVO extends NonEmptyStringVO
{

    /**
     * Constructor
     *
     * @param string|null $name Subscription name
     *
     * @throws InvalidSubscriptionNameException If provided name is invalid
     */
    public function __construct(?string $name)
    {

        try {
            parent::__construct($name);
        } catch (InvalidArgumentException) {
            throw new InvalidSubscriptionNameException();
        }
    }

    public static function fromNonEmptyString(NonEmptyStringVO $nonEmptyStringVO): self
    {
        return new self($nonEmptyStringVO->getValue());
    }

    /**
     * Get subscription name
     *
     * @return string Subscription name
     */
    public function getName(): string
    {
        return $this->getValue();
    }
}