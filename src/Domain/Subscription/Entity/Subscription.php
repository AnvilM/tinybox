<?php

declare(strict_types=1);

namespace App\Domain\Subscription\Entity;

use App\Domain\Group\Entity\Group;
use App\Domain\Outbound\Collection\UniqueOutboundsMap;
use App\Domain\Subscription\VO\SubscriptionNameVO;
use App\Domain\Subscription\VO\SubscriptionURLVO;

final class Subscription
{
    private SubscriptionURLVO $url;

    private Group $group;

    public function __construct(SubscriptionNameVO $name, SubscriptionURLVO $url, UniqueOutboundsMap $outbounds)
    {
        $this->url = $url;
        $this->group = new Group($name, $outbounds);
    }


    /**
     * Get subscription name as string
     *
     * @return string Subscription name as string
     */
    public function getNameString(): string
    {
        return $this->group->getNameString();
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


    /**
     * Get subscription outbounds value object
     *
     * @return UniqueOutboundsMap Subscription outbounds
     */
    public function getOutbounds(): UniqueOutboundsMap
    {
        return $this->group->getOutbounds();
    }


    /**
     * Set subscription outbounds
     *
     * @param UniqueOutboundsMap $outbounds Subscription outbounds
     */
    public function setOutbounds(UniqueOutboundsMap $outbounds): void
    {
        $this->group->setOutbounds($outbounds);
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
     * @return SubscriptionNameVO Subscription name value object
     */
    public function getNameVO(): SubscriptionNameVO
    {
        return SubscriptionNameVO::fromNonEmptyString($this->group->getName());
    }
}