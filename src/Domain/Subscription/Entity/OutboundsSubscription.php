<?php

declare(strict_types=1);

namespace App\Domain\Subscription\Entity;

use App\Domain\Group\Entity\Group;
use App\Domain\Outbound\Collection\UniqueOutboundsMap;
use App\Domain\Shared\VO\Shared\NonEmptyStringVO;
use App\Domain\Subscription\VO\SubscriptionURLVO;

final class OutboundsSubscription extends Subscription
{
    private Group $group;

    public function __construct(NonEmptyStringVO $name, SubscriptionURLVO $url, UniqueOutboundsMap $outbounds)
    {
        parent::__construct($name, $url);

        $this->group = new Group($name, $outbounds);
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
     * Convert subscription to array
     *
     * @return array Subscription as array
     */
    public function toArray(): array
    {
        return [
            'name' => $this->getNameString(),
            'url' => $this->getUrl(),
            'type' => 'outbounds',
            'outbounds' => $this->getOutbounds()->getIds()->toArray(),
        ];
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
}