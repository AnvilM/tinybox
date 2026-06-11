<?php

declare(strict_types=1);

namespace App\Application\Repository\Subscription\Shared\Builder;

use App\Application\Exception\Repository\Shared\UnableToGetListException;
use App\Application\Repository\Subscription\Shared\Builder\Builder\RawConfigSubscriptionVOBuilder;
use App\Application\Repository\Subscription\Shared\Builder\Builder\RawOutboundsSubscriptionVOBuilder;
use App\Domain\Subscription\Exception\UnsupportedSubscriptionTypeException;
use App\Domain\Subscription\VO\RawSubscription\RawSubscriptionVO;
use App\Domain\Subscription\VO\SubscriptionTypeVO;
use InvalidArgumentException;

final readonly class RawSubscriptionVOBuilder
{
    public function __construct(
        private RawConfigSubscriptionVOBuilder    $rawConfigSubscriptionVOBuilder,
        private RawOutboundsSubscriptionVOBuilder $rawOutboundsSubscriptionVOBuilder
    )
    {
    }

    /**
     * Build raw subscription value object from subscription JSON decoded array
     *
     * @throws InvalidArgumentException
     * @throws UnsupportedSubscriptionTypeException If subscription type is unsupported
     * @throws UnableToGetListException If unable to get list of all outbounds
     */
    public function build(array $rawSubscription): RawSubscriptionVO
    {
        $subscriptionType = SubscriptionTypeVO::tryFrom($rawSubscription['type'] ?? "")
            ?? throw new UnsupportedSubscriptionTypeException("Subscription type '{$rawSubscription['type']}' is not supported");


        return match ($subscriptionType) {
            SubscriptionTypeVO::Outbounds => $this->rawOutboundsSubscriptionVOBuilder->handle($rawSubscription),
            SubscriptionTypeVO::Config => $this->rawConfigSubscriptionVOBuilder->handle($rawSubscription),
            default => throw new UnsupportedSubscriptionTypeException("Unsupported subscription type '{$rawSubscription['type']}'")
        };
    }
}