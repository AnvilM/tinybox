<?php

declare(strict_types=1);

namespace App\Application\Repository\Subscription\Shared\Builder\Builder;

use App\Domain\Subscription\VO\RawSubscription\RawConfigSubscriptionVO;
use InvalidArgumentException;
use Throwable;

final readonly class RawConfigSubscriptionVOBuilder
{
    /**
     * Parse config subscription as JSON decoded array to raw config subscription value object
     *
     * @param array $jsonDecodedConfigSubscription Config subscription as JSON decoded array
     *
     * @return RawConfigSubscriptionVO Raw config subscription value object
     *
     * @throws InvalidArgumentException
     */
    public function handle(array $jsonDecodedConfigSubscription): RawConfigSubscriptionVO
    {
        try {
            return new RawConfigSubscriptionVO(
                $jsonDecodedConfigSubscription['name'],
                $jsonDecodedConfigSubscription['url'],
                $jsonDecodedConfigSubscription['type'],
                $jsonDecodedConfigSubscription['config'],
            );
        } catch (Throwable) {
            throw new InvalidArgumentException();
        }
    }
}