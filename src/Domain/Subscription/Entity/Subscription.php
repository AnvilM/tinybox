<?php

declare(strict_types=1);

namespace App\Domain\Subscription\Entity;

use App\Domain\Shared\VO\Shared\NonEmptyStringVO;
use App\Domain\Subscription\VO\SubscriptionURLVO;

abstract class Subscription
{
    private SubscriptionURLVO $url;

    private NonEmptyStringVO $name;

    public function __construct(NonEmptyStringVO $name, SubscriptionURLVO $url)
    {
        $this->url = $url;
        $this->name = $name;
    }


    /**
     * Get clone of subscription url as value object
     *
     * @return SubscriptionURLVO Subscription url value object
     */
    public function getUrlVO(): SubscriptionURLVO
    {
        return clone $this->url;
    }


    /**
     * Get clone of subscription name as value object
     *
     * @return NonEmptyStringVO Subscription name value object
     */
    public function getNameVO(): NonEmptyStringVO
    {
        return $this->name;
    }


    /**
     * Convert subscription to array
     *
     * @return array Subscription as array
     */
    public abstract function toArray(): array;


    /**
     * Get subscription name as string
     *
     * @return string Subscription name as string
     */
    public function getNameString(): string
    {
        return $this->name->getValue();
    }


    /**
     * Get subscription url
     *
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url->getUrl();
    }
}