<?php

declare(strict_types=1);

namespace App\Application\Services\Subscription\ListSubscriptions\Handler;

use App\Application\Exception\Repository\Shared\UnableToGetListException;
use App\Application\Repository\Subscription\GetSubscriptionListRepository;
use App\Domain\Shared\Exception\CriticalException;
use Psl\Collection\MutableMap;

final readonly class ListSubscriptionsHandler
{
    public function __construct(
        private GetSubscriptionListRepository $getSubscriptionListRepository,
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
        try {
            return $this->getSubscriptionListRepository->getSubscriptionsList()->toNameUrlMap();
        } catch (UnableToGetListException $e) {
            throw new CriticalException ("Unable to get subscriptions list: " . $e->getMessage(), $e->getDebugMessage());
        }
    }
}