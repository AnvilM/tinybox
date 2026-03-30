<?php

declare(strict_types=1);

namespace App\Application\Services\Subscription\ListSubscriptions\Handler;

use App\Application\Shared\Subscription\Shared\UseCase\ReadSubscriptionsList\ReadSubscriptionsListUseCase;
use App\Domain\Shared\Exception\CriticalException;
use Psl\Collection\MutableMap;

final readonly class ListSubscriptionsHandler
{
    public function __construct(
        private ReadSubscriptionsListUseCase $readSubscriptionsListUseCase,
    )
    {
    }

    /**
     * Read list subscriptions from file
     *
     * @return MutableMap<string, string> Map of subscription name => subscription url
     *
     * @throws CriticalException
     */
    public function handle(): MutableMap
    {
        return $this->readSubscriptionsListUseCase->handle()->toNameUrlMap();
    }
}