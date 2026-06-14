<?php

declare(strict_types=1);

namespace App\Domain\Subscription\Entity;

use App\Domain\Group\Entity\Group;
use App\Domain\Outbound\Collection\UniqueTagOutboundsMap;
use App\Domain\Shared\VO\Shared\NonEmptyStringVO;
use App\Domain\Subscription\VO\SubscriptionTypeVO;
use App\Domain\Subscription\VO\SubscriptionURLVO;

final class OutboundsSubscription extends Subscription
{
    private Group $group;

    public function __construct(NonEmptyStringVO $name, SubscriptionURLVO $url, UniqueTagOutboundsMap $outbounds)
    {
        parent::__construct($name, $url);

        $this->group = new Group($name, $outbounds);
    }

    /**
     * Set subscription outbounds
     *
     * @param UniqueTagOutboundsMap $outbounds Subscription outbounds
     */
    public function setOutbounds(UniqueTagOutboundsMap $outbounds): void
    {
        $this->group->setOutbounds($outbounds);
    }


    /**
     * Convert subscription to array
     *
     * @return array Subscription as array
     */
    public function toArray(): array
    {
        return [
            'name' => $this->getNameString(),
            'url' => $this->getUrl(),
            'type' => SubscriptionTypeVO::Outbounds->value,
            'outbounds' => $this->getOutbounds()->getIds()->toArray(),
        ];
    }


    /**
     * Get subscription outbounds value object
     *
     * @return UniqueTagOutboundsMap Subscription outbounds
     */
    public function getOutbounds(): UniqueTagOutboundsMap
    {
        return $this->group->getOutbounds();
    }
}