<?php

declare(strict_types=1);

namespace App\Domain\Subscription\Factory\FromRawSubscription;

use App\Domain\Subscription\Entity\OutboundsSubscription;
use App\Domain\Subscription\Entity\Subscription;
use App\Domain\Subscription\Exception\UnsupportedSubscriptionTypeException;
use App\Domain\Subscription\VO\RawConfigSubscriptionVO;
use App\Domain\Subscription\VO\RawOutboundsSubscriptionVO;
use App\Domain\Subscription\VO\RawSubscriptionVO;
use InvalidArgumentException;

final readonly class FromRawSubscriptionFactory
{
    /**
     * Create subscription entity from raw subscription value object
     *
     * @param RawSubscriptionVO $rawSubscriptionVO Raw subscription value object
     *
     * @return Subscription Subscription entity
     *
     * @throws InvalidArgumentException
     * @throws UnsupportedSubscriptionTypeException
     */
    public function fromRawSubscriptionVO(RawSubscriptionVO $rawSubscriptionVO): Subscription
    {
        if ($rawSubscriptionVO instanceof RawOutboundsSubscriptionVO) {
            return $this->fromRawOutboundsSubscription($rawOutboundVO);
        }

        if ($rawSubscriptionVO instanceof RawConfigSubscriptionVO) {
            return $this->fromRawConfigSubscription($rawOutboundVO);
        }

        throw new UnsupportedSubscriptionTypeException($rawSubscriptionVO->type);
    }
    
    private function fromRawOutboundsSubscription(RawOutboundsSubscriptionVO $rawOutboundsSubscriptionVO): OutboundsSubscription
    {
        return new OutboundsSubscription()
    }
}