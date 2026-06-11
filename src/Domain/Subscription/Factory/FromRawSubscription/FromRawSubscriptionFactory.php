<?php

declare(strict_types=1);

namespace App\Domain\Subscription\Factory\FromRawSubscription;

use App\Domain\Shared\VO\Shared\NonEmptyStringVO;
use App\Domain\Subscription\Entity\ConfigSubscription;
use App\Domain\Subscription\Entity\OutboundsSubscription;
use App\Domain\Subscription\Entity\Subscription;
use App\Domain\Subscription\Exception\InvalidSubscriptionURLException;
use App\Domain\Subscription\Exception\UnsupportedSubscriptionTypeException;
use App\Domain\Subscription\VO\RawSubscription\RawConfigSubscriptionVO;
use App\Domain\Subscription\VO\RawSubscription\RawOutboundsSubscriptionVO;
use App\Domain\Subscription\VO\RawSubscription\RawSubscriptionVO;
use App\Domain\Subscription\VO\SubscriptionURLVO;
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
     * @throws InvalidSubscriptionURLException
     */
    public function fromRawSubscriptionVO(RawSubscriptionVO $rawSubscriptionVO): Subscription
    {
        if ($rawSubscriptionVO instanceof RawOutboundsSubscriptionVO) {
            return $this->fromRawOutboundsSubscription($rawSubscriptionVO);
        }

        if ($rawSubscriptionVO instanceof RawConfigSubscriptionVO) {
            return $this->fromRawConfigSubscription($rawSubscriptionVO);
        }

        throw new UnsupportedSubscriptionTypeException($rawSubscriptionVO->type);
    }


    /**
     * @throws InvalidArgumentException
     * @throws InvalidSubscriptionURLException
     */
    private function fromRawOutboundsSubscription(RawOutboundsSubscriptionVO $rawOutboundsSubscriptionVO): OutboundsSubscription
    {
        return new OutboundsSubscription(
            new NonEmptyStringVO($rawOutboundsSubscriptionVO->name),
            new SubscriptionURLVO($rawOutboundsSubscriptionVO->url),
            $rawOutboundsSubscriptionVO->outbounds
        );
    }

    /**
     * @throws InvalidArgumentException
     * @throws InvalidSubscriptionURLException
     */
    private function fromRawConfigSubscription(RawConfigSubscriptionVO $rawConfigSubscription): ConfigSubscription
    {
        return new ConfigSubscription(
            new NonEmptyStringVO($rawConfigSubscription->name),
            new SubscriptionURLVO($rawConfigSubscription->url),
            new NonEmptyStringVO($rawConfigSubscription->config)
        );
    }
}