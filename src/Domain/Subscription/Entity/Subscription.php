<?php

declare(strict_types=1);

namespace App\Domain\Subscription\Entity;

use App\Domain\Shared\VO\Shared\SchemesIdsVO;
use App\Domain\Subscription\VO\SubscriptionNameVO;
use App\Domain\Subscription\VO\SubscriptionURLVO;

final readonly class Subscription
{
    private SubscriptionNameVO $name;
    private SubscriptionURLVO $url;
    private SchemesIdsVO $schemesIds;

    public function __construct(SubscriptionNameVO $name, SubscriptionURLVO $url, SchemesIdsVO $schemesIds)
    {
        $this->name = $name;
        $this->url = $url;
        $this->schemesIds = $schemesIds;
    }


    /**
     * Get subscription name
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name->getName();
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
     * Get subscription schemes value object
     *
     * @return SchemesIdsVO Subscription schemes
     */
    public function getSchemesIds(): SchemesIdsVO
    {
        return $this->schemesIds;
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
        return clone $this->name;
    }
}