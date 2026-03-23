<?php

declare(strict_types=1);

namespace App\Domain\Subscription\Entity;

use App\Domain\Scheme\Collection\UniqueSchemesMap;
use App\Domain\Subscription\VO\SubscriptionNameVO;
use App\Domain\Subscription\VO\SubscriptionURLVO;

final class Subscription
{
    private SubscriptionNameVO $name;
    private SubscriptionURLVO $url;
    private UniqueSchemesMap $schemes;

    public function __construct(SubscriptionNameVO $name, SubscriptionURLVO $url, UniqueSchemesMap $schemes)
    {
        $this->name = $name;
        $this->url = $url;
        $this->schemes = $schemes;
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
     * @return UniqueSchemesMap Subscription schemes
     */
    public function getSchemes(): UniqueSchemesMap
    {
        return $this->schemes;
    }


    /**
     * Set subscription schemes
     *
     * @param UniqueSchemesMap $schemes Subscription schemes
     */
    public function setSchemes(UniqueSchemesMap $schemes): void
    {
        $this->schemes = $schemes;
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